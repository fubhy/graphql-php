<?php

namespace Fubhy\GraphQL\Type;

use Fubhy\GraphQL\Schema;
use Fubhy\GraphQL\Type\Definition\EnumValueDefinition;
use Fubhy\GraphQL\Type\Definition\FieldArgument;
use Fubhy\GraphQL\Type\Definition\FieldDefinition;
use Fubhy\GraphQL\Type\Definition\InputObjectField;
use Fubhy\GraphQL\Type\Definition\Types\EnumType;
use Fubhy\GraphQL\Type\Definition\Types\InputObjectType;
use Fubhy\GraphQL\Type\Definition\Types\InterfaceType;
use Fubhy\GraphQL\Type\Definition\Types\ListModifier;
use Fubhy\GraphQL\Type\Definition\Types\ModifierInterface;
use Fubhy\GraphQL\Type\Definition\Types\NonNullModifier;
use Fubhy\GraphQL\Type\Definition\Types\ObjectType;
use Fubhy\GraphQL\Type\Definition\Types\ScalarTypeInterface;
use Fubhy\GraphQL\Type\Definition\Types\Type;
use Fubhy\GraphQL\Type\Definition\Types\TypeInterface;
use Fubhy\GraphQL\Type\Definition\Types\UnionType;
use Fubhy\GraphQL\Type\Directives\DirectiveInterface;

class Introspection
{
    const TYPEKIND_SCALAR = 0;
    const TYPEKIND_OBJECT = 1;
    const TYPEKIND_INTERFACE = 2;
    const TYPEKIND_UNION = 3;
    const TYPEKIND_ENUM = 4;
    const TYPEKIND_INPUT_OBJECT = 5;
    const TYPEKIND_LIST = 6;
    const TYPEKIND_NON_NULL = 7;

    /**
     * @var \Fubhy\GraphQL\Type\Definition\Types\ObjectType
     */
    protected static $schema;

    /**
     * @var \Fubhy\GraphQL\Type\Definition\Types\ObjectType
     */
    protected static $directive;

    /**
     * @var \Fubhy\GraphQL\Type\Definition\Types\ObjectType
     */
    protected static $type;

    /**
     * @var \Fubhy\GraphQL\Type\Definition\Types\ObjectType
     */
    protected static $field;

    /**
     * @var \Fubhy\GraphQL\Type\Definition\Types\ObjectType
     */
    protected static $inputValue;

    /**
     * @var \Fubhy\GraphQL\Type\Definition\Types\ObjectType
     */
    protected static $typeKind;

    /**
     * @var \Fubhy\GraphQL\Type\Definition\Types\ObjectType
     */
    protected static $enumValue;

    /**
     * @var \Fubhy\GraphQL\Type\Definition\FieldDefinition
     */
    protected static $typeNameMetaFieldDefinition;

    /**
     * @var \Fubhy\GraphQL\Type\Definition\FieldDefinition
     */
    protected static $schemaMetaFieldDefinition;

    /**
     * @var \Fubhy\GraphQL\Type\Definition\FieldDefinition
     */
    protected static $typeMetaFieldDefinition;

    /**
     * @param $source
     * @return array|null
     */
    public static function resolveSchemaTypes($source) {
        if ($source instanceof Schema) {
            return array_values($source->getTypeMap());
        }

        return NULL;
    }

    /**
     * @param $source
     * @return \Fubhy\GraphQL\Type\Definition\Types\ObjectType|null
     */
    public static function resolveSchemaQueryType($source) {
        if ($source instanceof Schema) {
            return $source->getQueryType();
        }

        return NULL;
    }

    /**
     * @param $source
     * @return \Fubhy\GraphQL\Type\Definition\Types\ObjectType|null
     */
    public static function resolveSchemaMutationType($source) {
        if ($source instanceof Schema) {
            return $source->getMutationType();
        }

        return NULL;
    }

    /**
     * @param $source
     * @return \Fubhy\GraphQL\Type\Directives\DirectiveInterface[]|null
     */
    public static function resolveSchemaDirectives($source) {
        if ($source instanceof Schema) {
            return $source->getDirectives();
        }

        return NULL;
    }

    /**
     * @return \Fubhy\GraphQL\Type\Definition\Types\ObjectType
     */
    public static function schema()
    {
        if (!isset(static::$schema)) {
            static::$schema = new ObjectType('__Schema', [
                'types' => [
                    'description' => 'A list of all types supported by this server.',
                    'type' => new NonNullModifier(new ListModifier(new NonNullModifier([__CLASS__, 'type']))),
                    'resolve' => [__CLASS__, 'resolveSchemaTypes'],
                ],
                'queryType' => [
                    'description' => 'The type that query operations will be rooted at.',
                    'type' => new NonNullModifier([__CLASS__, 'type']),
                    'resolve' => [__CLASS__, 'resolveSchemaQueryType'],
                ],
                'mutationType' => [
                    'description' => 'If this server supports mutation, the type that mutation operations will be rooted at.',
                    'type' => [__CLASS__, 'type'],
                    'resolve' => [__CLASS__, 'resolveSchemaMutationType'],
                ],
                'directives' => [
                    'description' => 'A list of all directives supported by this server.',
                    'type' => new NonNullModifier(new ListModifier(new NonNullModifier([__CLASS__, 'directive']))),
                    'resolve' => [__CLASS__, 'resolveSchemaDirectives'],
                ],
            ], [], NULL, 'A GraphQL Schema defines the capabilities of a GraphQL server. It exposes all available types and directives on the server, as well as the entry points for query and mutation operations.');
        }

        return static::$schema;
    }

    /**
     * @param $source
     * @return string
     */
    public static function resolveDirectiveName($source) {
        if ($source instanceof DirectiveInterface) {
            return $source->getName();
        }

        return NULL;
    }


    /**
     * @param $source
     * @return string
     */
    public static function resolveDirectiveDescription($source) {
        if ($source instanceof DirectiveInterface) {
            return $source->getDescription();
        }

        return NULL;
    }

    /**
     * @param $source
     * @return string
     */
    public static function resolveDirectiveArguments($source) {
        if ($source instanceof DirectiveInterface) {
            return $source->getArguments();
        }

        return NULL;
    }

    /**
     * @param $source
     * @return bool|null
     */
    public static function resolveDirectiveOnOperation($source) {
        if ($source instanceof DirectiveInterface) {
            return $source->onOperation();
        }

        return NULL;
    }

    /**
     * @param $source
     * @return bool|null
     */
    public static function resolveDirectiveOnFragment($source) {
        if ($source instanceof DirectiveInterface) {
            return $source->onFragment();
        }

        return NULL;
    }

    /**
     * @param $source
     * @return bool|null
     */
    public static function resolveDirectiveOnField($source) {
        if ($source instanceof DirectiveInterface) {
            return $source->onField();
        }

        return NULL;
    }

    /**
     * @return \Fubhy\GraphQL\Type\Definition\Types\ObjectType
     */
    public static function directive()
    {
        if (!isset(static::$directive)) {
            static::$directive = new ObjectType('__Directive', [
                'name' => [
                    'type' => new NonNullModifier(Type::stringType()),
                    'resolve' => [__CLASS__, 'resolveDirectiveName'],
                ],
                'description' => [
                    'type' => Type::stringType(),
                    'resolve' => [__CLASS__, 'resolveDirectiveDescription'],
                ],
                'args' => [
                    'type' => new NonNullModifier(new ListModifier(new NonNullModifier(self::inputValue()))),
                    'resolve' => [__CLASS__, 'resolveDirectiveArguments'],
                ],
                'onOperation' => [
                    'type' => new NonNullModifier(Type::booleanType()),
                    'resolve' => [__CLASS__, 'resolveDirectiveOnOperation'],
                ],
                'onFragment' => [
                    'type' => new NonNullModifier(Type::booleanType()),
                  'resolve' => [__CLASS__, 'resolveDirectiveOnFragment'],
                ],
                'onField' => [
                    'type' => new NonNullModifier(Type::booleanType()),
                    'resolve' => [__CLASS__, 'resolveDirectiveOnField'],
                ],
            ]);
        }

        return static::$directive;
    }

    /**
     * @param $source
     * @return int
     */
    public static function resolveTypeKind($source) {
        if ($source instanceof ScalarTypeInterface) {
            return static::TYPEKIND_SCALAR;
        }

        if ($source instanceof ObjectType) {
            return static::TYPEKIND_OBJECT;
        }

        if ($source instanceof EnumType) {
            return static::TYPEKIND_ENUM;
        }

        if ($source instanceof InputObjectType) {
            return static::TYPEKIND_INPUT_OBJECT;
        }

        if ($source instanceof InterfaceType) {
            return static::TYPEKIND_INTERFACE;
        }

        if ($source instanceof UnionType) {
            return static::TYPEKIND_UNION;
        }

        if ($source instanceof ListModifier) {
            return static::TYPEKIND_LIST;
        }

        if ($source instanceof NonNullModifier) {
            return static::TYPEKIND_NON_NULL;
        }

        throw new \LogicException(sprintf('Unknown kind of type %s.', (string) $source));
    }

    /**
     * @param $source
     * @return null|string
     */
    public static function resolveTypeName($source) {
        if ($source instanceof TypeInterface) {
            return $source->getName();
        }

        return NULL;
    }

    /**
     * @param $source
     * @return null|string
     */
    public static function resolveTypeDescription($source) {
        if ($source instanceof TypeInterface) {
            return $source->getDescription();
        }

        return NULL;
    }

    /**
     * @param $source
     * @param array $args
     * @return null|string
     */
    public static function resolveTypeFields($source, array $args) {
        if ($source instanceof ObjectType || $source instanceof InterfaceType) {
            $fields = $source->getFields();

            if (empty($args['includeDeprecated'])) {
                $fields = array_filter($fields, function (FieldDefinition $field) {
                    return !$field->getDeprecationReason();
                });
            }

            return array_values($fields);
        }

        return NULL;
    }

    /**
     * @param $source
     * @return null|string
     */
    public static function resolveTypeInterfaces($source) {
        if ($source instanceof ObjectType) {
            return $source->getInterfaces();
        }

        return NULL;
    }

    /**
     * @param $source
     * @return null|string
     */
    public static function resolveTypePossibleTypes($source) {
        if ($source instanceof InterfaceType || $source instanceof UnionType) {
            return $source->getPossibleTypes();
        }

        return NULL;
    }

    /**
     * @param $source
     * @param array $args
     * @return array
     */
    public static function resolveTypeEnumValues($source, array $args) {
        if ($source instanceof EnumType) {
            $values = $source->getValues();

            if (empty($args['includeDeprecated'])) {
                $values = array_filter($values, function (EnumValueDefinition $value) {
                    return !$value->getDeprecationReason();
                });
            }

            return array_values($values);
        }

        return NULL;
    }

    /**
     * @param $source
     * @return array|null
     */
    public static function resolveTypeEnumFields($source) {
        if ($source instanceof InputObjectType) {
            return array_values($source->getFields());
        }

        return NULL;
    }

    /**
     * @param $source
     * @return \Fubhy\GraphQL\Type\Definition\Types\TypeInterface
     */
    public static function resolveTypeOfType($source) {
        if ($source instanceof ModifierInterface) {
            return $source->getWrappedType();
        }
    }

    /**
     * @return \Fubhy\GraphQL\Type\Definition\Types\ObjectType
     */
    public static function type()
    {
        if (!isset(static::$type)) {
            static::$type = new ObjectType('__Type', [
                'kind' => [
                    'type' => new NonNullModifier([__CLASS__, 'typeKind']),
                    'resolve' => [__CLASS__, 'resolveTypeKind'],
                ],
                'name' => [
                    'type' => Type::stringType(),
                    'resolve' => [__CLASS__, 'resolveTypeName'],
                ],
                'description' => [
                    'type' => Type::stringType(),
                    'resolve' => [__CLASS__, 'resolveTypeDescription'],
                ],
                'fields' => [
                    'type' => new ListModifier(new NonNullModifier([__CLASS__, 'field'])),
                    'args' => [
                        'includeDeprecated' => [
                            'type' => Type::booleanType(),
                            'defaultValue' => FALSE,
                        ],
                    ],
                    'resolve' => [__CLASS__, 'resolveTypeFields'],
                ],
                'interfaces' => [
                    'type' => new ListModifier(new NonNullModifier([__CLASS__, 'type'])),
                    'resolve' => [__CLASS__, 'resolveTypeInterfaces'],
                ],
                'possibleTypes' => [
                    'type' => new ListModifier(new NonNullModifier([__CLASS__, 'type'])),
                    'resolve' => [__CLASS__, 'resolveTypePossibleTypes'],
                ],
                'enumValues' => [
                    'type' => new ListModifier(new NonNullModifier([__CLASS__, 'enumValue'])),
                    'args' => [
                        'includeDeprecated' => [
                            'type' => Type::booleanType(),
                            'defaultValue' => FALSE,
                        ],
                    ],
                    'resolve' => [__CLASS__, 'resolveTypeEnumValues'],
                ],
                'inputFields' => [
                    'type' => new ListModifier(new NonNullModifier([__CLASS__, 'inputValue'])),
                    'resolve' => [__CLASS__, 'resolveTypeInputFields'],
                ],
                'ofType' => [
                    'type' => [__CLASS__, 'type'],
                    'resolve' => [__CLASS__, 'resolveTypeOfType'],
                ],
            ]);
        }

        return static::$type;
    }

    /**
     * @param $source
     * @return null|string
     */
    public static function resolveFieldName($source) {
        if ($source instanceof FieldDefinition) {
            return $source->getName();
        }

        return NULL;
    }

    /**
     * @param $source
     * @return null|string
     */
    public static function resolveFieldDescription($source) {
        if ($source instanceof FieldDefinition) {
            return $source->getDescription();
        }

        return NULL;
    }

    /**
     * @param $source
     * @return null|string
     */
    public static function resolveFieldArguments($source) {
        if ($source instanceof FieldDefinition) {
            return $source->getArguments() ?: [];
        }

        return NULL;
    }

    /**
     * @param $source
     * @return null|string
     */
    public static function resolveFieldType($source) {
        if ($source instanceof FieldDefinition) {
            return $source->getType();
        }

        return NULL;
    }

    /**
     * @param $source
     * @return bool|null
     */
    public static function resolveFieldIsDeprecated($source) {
        if ($source instanceof FieldDefinition) {
            return (bool)$source->getDeprecationReason();
        }

        return NULL;
    }

    /**
     * @param $source
     * @return null|string
     */
    public static function resolveFieldDeprecationReason($source) {
        if ($source instanceof FieldDefinition) {
            return $source->getDeprecationReason();
        }

        return NULL;
    }

    /**
     * @return \Fubhy\GraphQL\Type\Definition\Types\ObjectType
     */
    public static function field()
    {
        if (!isset(static::$field)) {
            static::$field = new ObjectType('__Field', [
                'name' => [
                    'type' => new NonNullModifier(Type::stringType()),
                    'resolve' => [__CLASS__, 'resolveFieldName'],
                ],
                'description' => [
                    'type' => Type::stringType(),
                    'resolve' => [__CLASS__, 'resolveFieldDescription'],
                ],
                'args' => [
                    'type' => new NonNullModifier(new ListModifier(new NonNullModifier([__CLASS__, 'inputValue']))),
                    'resolve' => [__CLASS__, 'resolveFieldArguments'],
                ],
                'type' => [
                    'type' => new NonNullModifier([__CLASS__, 'type']),
                    'resolve' => [__CLASS__, 'resolveFieldType'],
                ],
                'isDeprecated' => [
                    'type' => new NonNullModifier(Type::booleanType()),
                    'resolve' => [__CLASS__, 'resolveFieldIsDeprecated'],
                ],
                'deprecationReason' => [
                    'type' => Type::stringType(),
                    'resolve' => [__CLASS__, 'resolveFieldDeprecationReason'],
                ],
            ]);
        }

        return static::$field;
    }

    /**
     * @param $source
     * @return null|string
     */
    public static function resolveInputValueName($source) {
        if ($source instanceof InputObjectField || $source instanceof FieldArgument) {
            return $source->getName();
        }

        return NULL;
    }

    /**
     * @param $source
     * @return null|string
     */
    public static function resolveInputValueDescription($source) {
        if ($source instanceof InputObjectField || $source instanceof FieldArgument) {
            return $source->getDescription();
        }

        return NULL;
    }

    /**
     * @param $source
     * @return \Fubhy\GraphQL\Type\Definition\Types\InputTypeInterface
     */
    public static function resolveInputValueType($source) {
        if ($source instanceof InputObjectField || $source instanceof FieldArgument) {
            return $source->getType();
        }

        return NULL;
    }

    /**
     * @param $source
     * @return \Fubhy\GraphQL\Type\Definition\Types\InputTypeInterface
     */
    public static function resolveInputValueDefaultValue($source) {
        if ($source instanceof InputObjectField || $source instanceof FieldArgument) {
            $defaultValue = $source->getDefaultValue();
            return isset($defaultValue) ? json_encode($defaultValue) : NULL;
        }

        return NULL;
    }

    /**
     * @return \Fubhy\GraphQL\Type\Definition\Types\ObjectType
     */
    public static function inputValue()
    {
        if (!isset(static::$inputValue)) {
            static::$inputValue = new ObjectType('__InputValue', [
                'name' => [
                    'type' => new NonNullModifier(Type::stringType()),
                    'resolve' => [__CLASS__, 'resolveInputValueName'],
                ],
                'description' => [
                    'type' => Type::stringType(),
                    'resolve' => [__CLASS__, 'resolveInputValueDescription'],
                ],
                'type' => [
                    'type' => new NonNullModifier([__CLASS__, 'type']),
                    'resolve' => [__CLASS__, 'resolveInputValueType'],
                ],
                'defaultValue' => [
                    'type' => Type::stringType(),
                    'resolve' => [__CLASS__, 'resolveInputValueDefaultValue'],
                ],
            ]);
        }

        return static::$inputValue;
    }

    /**
     * @param $source
     * @return string
     */
    public static function resolveEnumValueName($source) {
        if ($source instanceof EnumValueDefinition) {
            return $source->getName();
        }

        return NULL;
    }

    /**
     * @param $source
     * @return string
     */
    public static function resolveEnumValueDescription($source) {
        if ($source instanceof EnumValueDefinition) {
            return $source->getDescription();
        }

        return NULL;
    }

    /**
     * @param $source
     * @return string
     */
    public static function resolveEnumValueIsDeprecated($source) {
        if ($source instanceof EnumValueDefinition) {
            return (bool) $source->getDeprecationReason();
        }

        return NULL;
    }

    /**
     * @param $source
     * @return string
     */
    public static function resolveEnumValueDeprecationReason($source) {
        if ($source instanceof EnumValueDefinition) {
            return (bool) $source->getDeprecationReason();
        }

        return NULL;
    }

    /**
     * @return \Fubhy\GraphQL\Type\Definition\Types\ObjectType
     */
    public static function enumValue()
    {
        if (!isset(static::$enumValue)) {
            static::$enumValue = new ObjectType('__EnumValue', [
                'name' => [
                    'type' => new NonNullModifier(Type::stringType()),
                    'resolve' => [__CLASS__, 'resolveEnumValueName'],
                ],
                'description' => [
                    'type' => Type::stringType(),
                    'resolve' => [__CLASS__, 'resolveEnumValueDescription'],
                ],
                'isDeprecated' => [
                    'type' => new NonNullModifier(Type::booleanType()),
                    'resolve' => [__CLASS__, 'resolveEnumValueIsDeprecated'],
                ],
                'deprecationReason' => [
                    'type' => Type::stringType(),
                    'resolve' => [__CLASS__, 'resolveEnumValueDeprecationReason'],
                ],
            ]);
        }

        return static::$enumValue;
    }

    /**
     * @return \Fubhy\GraphQL\Type\Definition\Types\EnumType
     */
    public static function typeKind()
    {
        if (!isset(static::$typeKind)) {
            static::$typeKind = new EnumType('__TypeKind', [
                'SCALAR' => [
                    'value' => static::TYPEKIND_SCALAR,
                    'description' => 'Indicates this type is a scalar.',
                ],
                'OBJECT' => [
                    'value' => static::TYPEKIND_OBJECT,
                    'description' => 'Indicates this type is an object. `fields` and `interfaces` are valid fields.',
                ],
                'INTERFACE' => [
                    'value' => static::TYPEKIND_INTERFACE,
                    'description' => 'Indicates this type is an interface. `fields` and `possibleTypes` are valid fields.',
                ],
                'UNION' => [
                    'value' => static::TYPEKIND_UNION,
                    'description' => 'Indicates this type is a union. `possibleTypes` is a valid field.',
                ],
                'ENUM' => [
                    'value' => static::TYPEKIND_ENUM,
                    'description' => 'Indicates this type is an enum. `enumValues` is a valid field.',
                ],
                'INPUT_OBJECT' => [
                    'value' => static::TYPEKIND_INPUT_OBJECT,
                    'description' => 'Indicates this type is an input object. `inputFields` is a valid field.',
                ],
                'LIST' => [
                    'value' => static::TYPEKIND_LIST,
                    'description' => 'Indicates this type is a list. `ofType` is a valid field.',
                ],
                'NON_NULL' => [
                    'value' => static::TYPEKIND_NON_NULL,
                    'description' => 'Indicates this type is a non-null. `ofType` is a valid field.',
                ],
            ], 'An enum describing what kind of type a given __Type is.');
        }

        return static::$typeKind;
    }

    /**
     * @param $a
     * @param $b
     * @param $c
     * @param $d
     * @param $e
     * @param $f
     * @param $schema
     * @return mixed
     */
    public static function resolveSchemaMetaField($a, $b, $c, $d, $e, $f, $schema) {
        return $schema;
    }

    /**
     * @return \Fubhy\GraphQL\Type\Definition\FieldDefinition
     */
    public static function schemaMetaFieldDefinition()
    {
        if (!isset(static::$schemaMetaFieldDefinition)) {
            static::$schemaMetaFieldDefinition = new FieldDefinition([
                'name' => '__schema',
                'type' => new NonNullModifier([__CLASS__, 'schema']),
                'description' => 'Access the current type schema of this server.',
                'args' => [],
                'resolve' => [__CLASS__, 'resolveSchemaMetaField']
            ]);
        }

        return static::$schemaMetaFieldDefinition;
    }

    /**
     * @param $a
     * @param array $args
     * @param $b
     * @param $c
     * @param $d
     * @param $e
     * @param \Fubhy\GraphQL\Schema $schema
     * @return \Fubhy\GraphQL\Type\Definition\Types\TypeInterface
     */
    public static function resolveTypeMetaField($a, array $args, $b, $c, $d, $e, Schema $schema) {
        return $schema->getType($args['name']);
    }

    /**
     * @return \Fubhy\GraphQL\Type\Definition\FieldDefinition
     */
    public static function typeMetaFieldDefinition()
    {
        if (!isset(static::$typeMetaFieldDefinition)) {
            static::$typeMetaFieldDefinition = new FieldDefinition([
                'name' => '__type',
                'type' => [__CLASS__, 'type'],
                'description' => 'Request the type information of a single type.',
                'args' => [[
                    'name' => 'name',
                    'type' => new NonNullModifier(Type::stringType())
                ]],
                'resolve' => [__CLASS__, 'resolveTypeMetaField'],
            ]);
        }
        return static::$typeMetaFieldDefinition;
    }

    /**
     * @param $a
     * @param $b
     * @param $c
     * @param $d
     * @param $e
     * @param \Fubhy\GraphQL\Type\Definition\Types\TypeInterface $parentType
     * @return string
     */
    public static function resolveTypeNameMetaField($a, $b, $c, $d, $e, TypeInterface $parentType) {
        return $parentType->getName();
    }

    /**
     * @return \Fubhy\GraphQL\Type\Definition\FieldDefinition
     */
    public static function typeNameMetaFieldDefinition()
    {
        if (!isset(static::$typeNameMetaFieldDefinition)) {
            static::$typeNameMetaFieldDefinition = new FieldDefinition([
                'name' => '__typename',
                'type' => new NonNullModifier(Type::stringType()),
                'description' => 'The name of the current Object type at runtime.',
                'args' => [],
                'resolve' => [__CLASS__, 'resolveTypeNameMetaField'],
            ]);
        }

        return static::$typeNameMetaFieldDefinition;
    }
}
