<?php

namespace Fubhy\GraphQL\Language;

abstract class Node
{
    const KIND_NAME = 'Name';
    const KIND_DOCUMENT = 'Document';
    const KIND_OPERATION_DEFINITION = 'OperationDefinition';
    const KIND_VARIABLE_DEFINITION = 'VariableDefinition';
    const KIND_VARIABLE = 'Variable';
    const KIND_SELECTION_SET = 'SelectionSet';
    const KIND_FIELD = 'Field';
    const KIND_ARGUMENT = 'Argument';
    const KIND_FRAGMENT_SPREAD = 'FragmentSpread';
    const KIND_INLINE_FRAGMENT = 'InlineFragment';
    const KIND_FRAGMENT_DEFINITION = 'FragmentDefinition';
    const KIND_INT_VALUE = 'IntValue';
    const KIND_FLOAT_VALUE = 'FloatValue';
    const KIND_STRING_VALUE = 'StringValue';
    const KIND_BOOLEAN_VALUE = 'BooleanValue';
    const KIND_ENUM_VALUE = 'EnumValue';
    const KIND_ARRAY_VALUE = 'ArrayValue';
    const KIND_OBJECT_VALUE = 'ObjectValue';
    const KIND_OBJECT_FIELD = 'ObjectField';
    const KIND_DIRECTIVE = 'Directive';
    const KIND_TYPE = 'Type';
    const KIND_LIST_TYPE = 'ListType';
    const KIND_NAMED_TYPE = 'NamedType';
    const KIND_NON_NULL_TYPE = 'NonNullType';

    const KIND = NULL;

    /**
     * @var \Fubhy\GraphQL\Language\Location|null
     */
    protected $location;

    /**
     * @var array
     */
    protected $attributes;

    /**
     * Constructor.
     *
     * @param \Fubhy\GraphQL\Language\Location|null $location
     * @param array $attributes
     */
    public function __construct(Location $location = NULL, array $attributes = [])
    {
        $this->location = $location;
        $this->attributes = $attributes;
    }

    /**
     * @return Location|null
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function has($key)
    {
        return array_key_exists($key, $this->attributes);
    }

    /**
     * @param string $key
     * @param null $default
     *
     * @return mixed
     */
    public function get($key, $default = NULL)
    {
        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        }

        return $default;
    }

    /**
     * @param string $key
     * @param mixed $value
     *
     * @return $this
     */
    public function set($key, $value)
    {
        $this->attributes[$key] = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return json_encode($this);
    }
}
