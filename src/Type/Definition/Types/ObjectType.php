<?php

namespace Fubhy\GraphQL\Type\Definition\Types;

use Fubhy\GraphQL\Type\Definition\FieldDefinition;

/**
 * Object Type Definition.
 *
 * Almost all of the GraphQL types you define will be object types. Object types
 * have a name, but most importantly describe their fields.
 *
 * When two types need to refer to each other, or a type needs to refer to
 * itself in a field, you can use a function expression (aka a closure or a
 * thunk) to supply the fields lazily.
 */
class ObjectType extends Type implements OutputTypeInterface, CompositeTypeInterface, NullableTypeInterface, UnmodifiedTypeInterface
{
    /**
     * @var array
     */
    protected $fields;

    /**
     * @var \Fubhy\GraphQL\Type\Definition\Types\InterfaceType[]
     */
    protected $interfaces;

    /**
     * @var \Fubhy\GraphQL\Type\Definition\FieldDefinition[]
     */
    protected $fieldMap;

    /**
     * @var callable|null
     */
    protected $isTypeOf;

    /**
     * Constructor.
     *
     * @param string $name
     * @param array $fields
     * @param \Fubhy\GraphQL\Type\Definition\Types\InterfaceType[] $interfaces
     * @param callable|null $isTypeOf
     * @param string|null $description
     */
    public function __construct($name, array $fields = [], array $interfaces = [], callable $isTypeOf = NULL, $description = NULL)
    {
        parent::__construct($name, $description);

        $this->fields = $fields;
        $this->interfaces = $interfaces;
        $this->isTypeOf = $isTypeOf;

        foreach ($this->interfaces as $interface) {
            $interface->addPossibleType($this);
        }
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

            unset($this->fields);
        }

        return $this->fieldMap;
    }

    /**
     * @param array $fields
     */
    public function setFields(array $fields) {
        unset($this->fields);
        $this->fieldMap = $fields;
    }

    /**
     * @param string $field
     *
     * @return \Fubhy\GraphQL\Type\Definition\FieldDefinition
     */
    public function getField($field)
    {
        $fields = $this->getFields();
        if (!isset($fields[$field])) {
            throw new \LogicException(sprintf('Undefined field %s.', $field));
        }

        return $fields[$field];
    }

    /**
     * @return \Fubhy\GraphQL\Type\Definition\Types\InterfaceType[]
     */
    public function getInterfaces()
    {
        return isset($this->interfaces) ? $this->interfaces : [];
    }

    /**
     * @param mixed $value
     *
     * @return bool|null
     */
    public function isTypeOf($value)
    {
        return isset($this->isTypeOf) ? call_user_func($this->isTypeOf, $value) : NULL;
    }
}
