<?php

namespace Fubhy\GraphQL\Type\Definition\Types;

use Fubhy\GraphQL\Type\Definition\FieldDefinition;

/**
 * Interface Type Definition.
 *
 * When a field can return one of a heterogeneous set of types, a Interface type
 * is used to describe what types are possible, what fields are in common across
 * all types, as well as a function to determine which type is actually used
 * when the field is resolved.
 */
class InterfaceType extends AbstractType implements
    OutputTypeInterface,
    CompositeTypeInterface,
    NullableTypeInterface
{
    /**
     * @var array
     */
    protected $fields;

    /**
     * @var callable|null
     */
    protected $typeResolver;

    /**
     * @var \Fubhy\GraphQL\Type\Definition\Types\TypeInterface[]
     */
    protected $types = [];

    /**
     * @var \Fubhy\GraphQL\Type\Definition\FieldDefinition[]
     */
    protected $fieldMap;

    /**
     * Constructor.
     *
     * @param string $name
     * @param array $fields
     * @param callable|null $typeResolver
     * @param string|null $description
     */
    public function __construct($name, array $fields = [], callable $typeResolver = NULL, $description = NULL)
    {
        parent::__construct($name, $description);

        $this->fields = $fields;
        $this->typeResolver = $typeResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return \Fubhy\GraphQL\Type\Definition\FieldDefinition[]
     */
    public function getFields()
    {
        if (!isset($this->fieldMap)) {
            $this->fieldMap = [];
            foreach ($this->fields as $name => $field) {
                if (!isset($field['name'])) {
                    $field['name'] = $name;
                }

                $this->fieldMap[$name] = new FieldDefinition($field);
            }
        }

        return $this->fieldMap;
    }

    /**
     * {@inheritdoc}
     */
    public function getPossibleTypes()
    {
        return $this->types;
    }

    /**
     * {@inheritdoc}
     */
    public function addPossibleType(ObjectType $type)
    {
        $this->types = array_unique(array_merge($this->types, [$type]));
    }

    /**
     * {@inheritdoc}
     */
    public function isPossibleType(ObjectType $type)
    {
        return in_array($type, $this->types, TRUE);
    }

    /**
     * {@inheritdoc}
     */
    public function resolveType($value)
    {
        if (isset($this->typeResolver)) {
            return call_user_func($this->typeResolver, $value);
        }

        return $this->getTypeOf($value);
    }
}
