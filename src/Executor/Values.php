<?php

namespace Fubhy\GraphQL\Executor;

use Fubhy\GraphQL\Language\Node;
use Fubhy\GraphQL\Language\Node\VariableDefinition;
use Fubhy\GraphQL\Schema;
use Fubhy\GraphQL\Type\Definition\Types\TypeInterface;
use Fubhy\GraphQL\Type\Directives\DirectiveInterface;
use Fubhy\GraphQL\Type\Definition\FieldDefinition;
use Fubhy\GraphQL\Type\Definition\Types\EnumType;
use Fubhy\GraphQL\Type\Definition\Types\InputObjectType;
use Fubhy\GraphQL\Type\Definition\Types\ListModifier;
use Fubhy\GraphQL\Type\Definition\Types\NonNullModifier;
use Fubhy\GraphQL\Type\Definition\Types\ScalarType;
use Fubhy\GraphQL\Utility\TypeInfo;

class Values
{
    /**
     * Prepares an object map of variables of the correct type based on the
     * provided variable definitions and arbitrary input. If the input cannot be
     * coerced to match the variable definitions, a Error will be thrown.
     *
     * @param Schema $schema
     * @param array $asts
     * @param array $inputs
     *
     * @return array
     *
     * @throws \Exception
     */
    public static function getVariableValues(Schema $schema, array $asts, array $inputs)
    {
        $values = [];
        foreach ($asts as $ast) {
            $variable = $ast->get('variable')->get('name')->get('value');
            $values[$variable] = self::getvariableValue($schema, $ast, isset($inputs[$variable]) ? $inputs[$variable] : NULL);
        }
        return $values;
    }

    /**
     * Prepares an object map of argument values given a list of argument
     * definitions and list of argument AST nodes.
     *
     * @param $arguments
     * @param $asts
     * @param $variables
     *
     * @return array|null
     */
    public static function getArgumentValues($arguments, $asts, $variables)
    {
        if (!$arguments || count($arguments) === 0) {
            return NULL;
        }

        $map = array_reduce($asts, function ($carry, $argument) {
            $carry[$argument->get('name')->get('value')] = $argument;
            return $carry;
        }, []);

        $result = [];
        foreach ($arguments as $argument) {
            $name = $argument->getName();
            $value = isset($map[$name]) ? $map[$name]->get('value') : NULL;
            $result[$name] = self::coerceValueAST($argument->getType(), $value, $variables);
        }
        return $result;
    }

    /**
     * @param DirectiveInterface $definition
     * @param $directives
     * @param $variables
     *
     * @return array|mixed|null|string
     */
    public static function getDirectiveValue(DirectiveInterface $definition, $directives, $variables)
    {
        $ast = NULL;
        if ($directives) {
            foreach ($directives as $directive) {
                if ($directive->get('name')->get('value') === $definition->getName()) {
                    $ast = $directive;
                    break;
                }
            }
        }
        if ($ast) {
            if (!$definition->getType()) {
                return NULL;
            }

            return self::coerceValueAST($definition->getType(), $ast->get('value'), $variables);
        }
    }

    /**
     * Given a variable definition, and any value of input, return a value which
     * adheres to the variable definition, or throw an error.
     *
     * @param Schema $schema
     * @param VariableDefinition $definition
     * @param $input
     *
     * @return array|mixed|null|string
     *
     * @throws \Exception
     */
    protected static function getVariableValue(Schema $schema, VariableDefinition $definition, $input)
    {
        $type = TypeInfo::typeFromAST($schema, $definition->get('type'));
        if (!$type) {
            return NULL;
        }

        if (self::isValidValue($type, $input)) {
            if (!isset($input)) {
                $default = $definition->get('defaultValue');

                if ($default) {
                    return self::coerceValueAST($type, $default);
                }
            }

            return self::coerceValue($type, $input);
        }

        // @todo Fix exception message once printer is ported.
        throw new \Exception(sprintf('Variable $%s expected value of different type.', $definition->get('variable')->get('name')->get('value')));
    }

    /**
     * Given a type and any value, return true if that value is valid.
     *
     * @param TypeInterface $type
     * @param $value
     *
     * @return bool
     */
    protected static function isValidValue(TypeInterface $type, $value)
    {
        if ($type instanceof NonNullModifier) {
            if (NULL === $value) {
                return FALSE;
            }

            return self::isValidValue($type->getWrappedType(), $value);
        }

        if ($value === NULL) {
            return TRUE;
        }

        if ($type instanceof ListModifier) {
            $itemType = $type->getWrappedType();
            if (is_array($value)) {
                foreach ($value as $item) {
                    if (!self::isValidValue($itemType, $item)) {
                        return FALSE;
                    }
                }

                return TRUE;
            } else {
                return self::isValidValue($itemType, $value);
            }
        }

        if ($type instanceof InputObjectType) {
            $fields = $type->getFields();
            foreach ($fields as $fieldName => $field) {
                if (!self::isValidValue($field->getType(), isset($value[$fieldName]) ? $value[$fieldName] : NULL)) {
                    return FALSE;
                }
            }

            return TRUE;
        }

        if ($type instanceof ScalarType || $type instanceof EnumType) {
            return NULL !== $type->coerce($value);
        }

        return FALSE;
    }

    /**
     * Given a type and any value, return a runtime value coerced to match the
     * type.
     *
     * @param TypeInterface $type
     * @param $value
     *
     * @return array|mixed|null|string
     */
    protected static function coerceValue(TypeInterface $type, $value)
    {
        if ($type instanceof NonNullModifier) {
            // Note: we're not checking that the result of coerceValue is non-null.
            // We only call this function after calling isValidValue.
            return self::coerceValue($type->getWrappedType(), $value);
        }

        if (!isset($value)) {
            return NULL;
        }

        if ($type instanceof ListModifier) {
            $itemType = $type->getWrappedType();
            if (is_array($value)) {
                return array_map(function ($item) use ($itemType) {
                    return Values::coerceValue($itemType, $item);
                }, $value);
            } else {
                return [self::coerceValue($itemType, $value)];
            }
        }

        if ($type instanceof InputObjectType) {
            $fields = $type->getFields();
            $object = [];
            foreach ($fields as $fieldName => $field) {
                $fieldValue = self::coerceValue($field->getType(), $value[$fieldName]);
                $object[$fieldName] = $fieldValue === NULL ? $field->getDefaultValue() : $fieldValue;
            }

            return $object;
        }

        if ($type instanceof ScalarType || $type instanceof EnumType) {
            $coerced = $type->coerce($value);
            if (NULL !== $coerced) {
                return $coerced;
            }
        }

        return NULL;
    }

    /**
     * Given a type and a value AST node known to match this type, build a
     * runtime value.
     *
     * @param TypeInterface $type
     * @param $ast
     * @param null $variables
     *
     * @return array|mixed|null|string
     */
    protected static function coerceValueAST(TypeInterface $type, $ast, $variables = NULL)
    {
        if ($type instanceof NonNullModifier) {
            // Note: we're not checking that the result of coerceValueAST is non-null.
            // We're assuming that this query has been validated and the value used
            // here is of the correct type.
            return self::coerceValueAST($type->getWrappedType(), $ast, $variables);
        }

        if (!$ast) {
            return NULL;
        }

        if ($ast::KIND === Node::KIND_VARIABLE) {
            $variableName = $ast->get('name')->get('value');

            if (!isset($variables, $variables[$variableName])) {
                return NULL;
            }

            // Note: we're not doing any checking that this variable is correct. We're
            // assuming that this query has been validated and the variable usage here
            // is of the correct type.
            return $variables[$variableName];
        }

        if ($type instanceof ListModifier) {
            $itemType = $type->getWrappedType();

            if ($ast::KIND === Node::KIND_ARRAY_VALUE) {
                $tmp = [];
                foreach ($ast->get('values') as $itemAST) {
                    $tmp[] = self::coerceValueAST($itemType, $itemAST, $variables);
                }

                return $tmp;
            } else {
                return [self::coerceValueAST($itemType, $ast, $variables)];
            }
        }

        if ($type instanceof InputObjectType) {
            $fields = $type->getFields();

            if ($ast::KIND !== Node::KIND_OBJECT_VALUE) {
                return NULL;
            }

            $asts = array_reduce($ast->get('fields'), function ($carry, $field) {
                $carry[$field->get('name')->get('value')] = $field;
                return $carry;
            }, []);

            $object = [];
            foreach ($fields as $name => $item) {
                $field = $asts[$name];
                $fieldValue = self::coerceValueAST($item->getType(), $field ? $field->get('value') : NULL, $variables);
                $object[$name] = $fieldValue === NULL ? $item->getDefaultValue() : $fieldValue;
            }

            return $object;
        }

        if ($type instanceof ScalarType || $type instanceof EnumType) {
            $coerced = $type->coerceLiteral($ast);

            if (isset($coerced)) {
                return $coerced;
            }
        }

        return NULL;
    }
}
