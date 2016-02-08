<?php

namespace Fubhy\GraphQL\Utility;

use Fubhy\GraphQL\Language\Node;
use Fubhy\GraphQL\Language\Node\Field;
use Fubhy\GraphQL\Language\Node\ListType;
use Fubhy\GraphQL\Language\Node\NamedType;
use Fubhy\GraphQL\Language\Node\NonNullType;
use Fubhy\GraphQL\Schema;
use Fubhy\GraphQL\Type\Definition\FieldDefinition;
use Fubhy\GraphQL\Type\Definition\Types\InputObjectType;
use Fubhy\GraphQL\Type\Definition\Types\InterfaceType;
use Fubhy\GraphQL\Type\Definition\Types\ListModifier;
use Fubhy\GraphQL\Type\Definition\Types\NonNullModifier;
use Fubhy\GraphQL\Type\Definition\Types\ObjectType;
use Fubhy\GraphQL\Type\Definition\Types\Type;
use Fubhy\GraphQL\Type\Definition\Types\UnionType;
use Fubhy\GraphQL\Type\Introspection;

class TypeInfo
{
    /**
     * @var Schema
     */
    protected $_schema;

    /**
     * @var \Fubhy\GraphQL\Type\Directives\DirectiveInterface
     */
    protected $directive;

    /**
     * @var \SplStack<OutputType>
     */
    protected $typeStack;

    /**
     * @var \SplStack<CompositeType>
     */
    protected $parentTypeStack;

    /**
     * @var \SplStack<InputType>
     */
    protected $inputTypeStack;

    /**
     * @var \SplStack<FieldDefinition>
     */
    protected $fieldDefinitionStack;

    /**
     * @param Schema $schema
     * @param $inputType
     *
     * @return ListModifier|NonNullModifier|\Fubhy\GraphQL\Type\Definition\Types\TypeInterface|null
     */
    public static function typeFromAST(Schema $schema, $inputType)
    {
        if ($inputType instanceof ListType) {
            $innerType = self::typeFromAST($schema, $inputType->get('type'));
            return $innerType ? new ListModifier($innerType) : NULL;
        }

        if ($inputType instanceof NonNullType) {
            $innerType = self::typeFromAST($schema, $inputType->get('type'));
            return $innerType ? new NonNullModifier($innerType) : NULL;
        }

        if (!($inputType instanceof NamedType)) {
            throw new \LogicException('Must be a type name.');
        }

        return $schema->getType($inputType->get('name')->get('value'));
    }

    /**
     * Not exactly the same as the executor's definition of getFieldDef, in this
     * statically evaluated environment we do not always have an Object type,
     * and need to handle Interface and Union types.
     *
     * @param Schema $schema
     * @param Type $parentType
     * @param Field $fieldAST
     *
     * @return FieldDefinition
     */
    static protected function getFieldDefinition(Schema $schema, Type $parentType, Field $fieldAST)
    {
        $name = $fieldAST->get('name')->get('value');
        $schemaMeta = Introspection::schemaMetaFieldDefinition();
        if ($name === $schemaMeta->getName() && $schema->getQueryType() === $parentType) {
            return $schemaMeta;
        }

        $typeMeta = Introspection::typeMetaFieldDefinition();
        if ($name === $typeMeta->getName() && $schema->getQueryType() === $parentType) {
            return $typeMeta;
        }

        $typeNameMeta = Introspection::typeNameMetaFieldDefinition();
        if ($name === $typeNameMeta->getName() && ($parentType instanceof ObjectType || $parentType instanceof InterfaceType || $parentType instanceof UnionType)) {
            return $typeNameMeta;
        }

        if ($parentType instanceof ObjectType || $parentType instanceof InterfaceType) {
            $fields = $parentType->getFields();
            return isset($fields[$name]) ? $fields[$name] : NULL;
        }

        return NULL;
    }

    /**
     * Constructor.
     *
     * @param Schema $schema
     */
    public function __construct(Schema $schema)
    {
        $this->_schema = $schema;
        $this->typeStack = [];
        $this->parentTypeStack = [];
        $this->inputTypeStack = [];
        $this->fieldDefinitionStack = [];
    }

    /**
     * @return Type
     */
    protected function getType()
    {
        if (!empty($this->typeStack)) {
            return $this->typeStack[count($this->typeStack) - 1];
        }

        return NULL;
    }

    /**
     * @return Type
     */
    protected function getParentType()
    {
        if (!empty($this->parentTypeStack)) {
            return $this->parentTypeStack[count($this->parentTypeStack) - 1];
        }
        return NULL;
    }

    /**
     * @return mixed|null
     */
    protected function getInputType()
    {
        if (!empty($this->inputTypeStack)) {
            return $this->inputTypeStack[count($this->inputTypeStack) - 1];
        }
        return NULL;
    }

    /**
     * @param Node $node
     */
    protected function enter(Node $node)
    {
        $schema = $this->_schema;

        switch ($node::KIND) {
            case Node::KIND_SELECTION_SET:
                $rawType = $this->getType();
                $rawType = isset($rawType) ? $rawType->getUnmodifiedType() : NULL;
                $compositeType = NULL;
                if (isset($rawType) && $rawType->isCompositeType()) {
                    $compositeType = $rawType;
                }
                array_push($this->parentTypeStack, $compositeType);
                break;

            case Node::KIND_DIRECTIVE:
                $this->directive = $schema->getDirective($node->get('name')->get('value'));
                break;

            case Node::KIND_FIELD:
                $parentType = $this->getParentType();
                $fieldDefinition = NULL;
                if (isset($parentType)) {
                    $fieldDefinition = self::getFieldDefinition($schema, $parentType, $node);
                }
                array_push($this->fieldDefinitionStack, $fieldDefinition);
                array_push($this->typeStack, $fieldDefinition ? $fieldDefinition->getType() : NULL);
                break;

            case Node::KIND_OPERATION_DEFINITION:
                $type = NULL;
                if ($node->get('operation') === 'query') {
                    $type = $schema->getQueryType();
                } else if ($node->get('operation') === 'mutation') {
                    $type = $schema->getMutationType();
                }
                array_push($this->typeStack, $type);
                break;

            case Node::KIND_INLINE_FRAGMENT:
            case Node::KIND_FRAGMENT_DEFINITION:
                $type = $schema->getType($node->get('typeCondition')->get('value'));
                array_push($this->typeStack, $type);
                break;

            case Node::KIND_VARIABLE_DEFINITION:
                array_push($this->inputTypeStack, self::typeFromAST($schema, $node->get('type')));
                break;

            case Node::KIND_ARGUMENT:
                if (!empty($this->fieldDefinitionStack)) {
                    $field = $this->fieldDefinitionStack[count($this->fieldDefinitionStack) - 1];
                }
                else {
                    $field = NULL;
                }

                $argType = NULL;
                if ($field) {
                    $argDefinition = NULL;
                    foreach ($field->getArguments() as $arg) {
                        if ($arg->getName() === $node->get('name')->get('value')) {
                            $argDefinition = $arg;
                        }
                        break;
                    }

                    if ($argDefinition) {
                        $argType = $argDefinition->getType();
                    }
                }
                array_push($this->inputTypeStack, $argType);
                break;

            case Node::KIND_ARRAY_VALUE:
                $arrayType = $this->getInputType();
                $arrayType = isset($arrayType) ? $arrayType->getNullableType() : NULL;
                array_push(
                    $this->inputTypeStack,
                    $arrayType instanceof ListModifier ? $arrayType->getWrappedType() : NULL
                );
                break;

            case Node::KIND_OBJECT_FIELD:
                $objectType = $this->getInputType();
                $objectType = isset($objectType) ? $objectType->getUnmodifiedType() : NULL;
                $fieldType = NULL;
                if ($objectType instanceof InputObjectType) {
                    $tmp = $objectType->getFields();
                    $inputField = isset($tmp[$node->get('name')->get('value')]) ? $tmp[$node->get('name')->get('value')] : NULL;
                    $fieldType = $inputField ? $inputField->getType() : NULL;
                }
                array_push($this->inputTypeStack, $fieldType);
            break;
        }
    }

    /**
     * @param Node $node
     */
    protected function leave(Node $node)
    {
        switch ($node::KIND) {
            case Node::KIND_SELECTION_SET:
                array_pop($this->parentTypeStack);
                break;
            case Node::KIND_FIELD:
                array_pop($this->fieldDefinitionStack);
                array_pop($this->typeStack);
                break;
            case Node::KIND_DIRECTIVE:
                $this->directive = NULL;
                break;
            case Node::KIND_OPERATION_DEFINITION:
            case Node::KIND_INLINE_FRAGMENT:
            case Node::KIND_FRAGMENT_DEFINITION:
                array_pop($this->typeStack);
                break;
            case Node::KIND_VARIABLE_DEFINITION:
                array_pop($this->inputTypeStack);
                break;
            case Node::KIND_ARGUMENT:
                array_pop($this->inputTypeStack);
                break;
            case Node::KIND_ARRAY_VALUE:
            case Node::KIND_OBJECT_FIELD:
                array_pop($this->inputTypeStack);
                break;
        }
    }
}
