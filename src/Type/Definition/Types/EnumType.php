<?php

namespace Fubhy\GraphQL\Type\Definition\Types;

use Fubhy\GraphQL\Language\Node;
use Fubhy\GraphQL\Language\Node\EnumValue;
use Fubhy\GraphQL\Type\Definition\EnumValueDefinition;

/**
 * Enum Type Definition.
 *
 * Some leaf values of requests and input values are Enums. GraphQL serializes
 * Enum values as strings, however internally Enums can be represented by any
 * kind of type, often integers.
 *
 * Note: If a value is not provided in a definition, the name of the enum value
 * will be used as it's internal value.
 */
class EnumType extends Type implements InputTypeInterface, OutputTypeInterface, LeafTypeInterface, NullableTypeInterface, UnmodifiedTypeInterface
{
    /**
     * @var array
     */
    protected $values;

    /**
     * @var \Fubhy\GraphQL\Type\Definition\EnumValueDefinition[]
     */
    protected $valueMap;

    /**
     * @var \Fubhy\GraphQL\Type\Definition\EnumValueDefinition[]
     */
    protected $valueLookup;

    /**
     * @var \Fubhy\GraphQL\Type\Definition\EnumValueDefinition[]
     */
    protected $nameLookup;

    /**
     * Constructor.
     *
     * @param string $name
     * @param array $values
     * @param string|null $description
     */
    public function __construct($name, array $values = [], $description = NULL)
    {
        parent::__construct($name, $description);

        $this->values = $values;
    }

    /**
     * @return \Fubhy\GraphQL\Type\Definition\EnumValueDefinition[]
     */
    public function getValues()
    {
        if (!isset($this->valueMap)) {
            $this->valueMap = [];
            foreach ($this->values as $name => $value) {
                $value['name'] = $name;

                if (!array_key_exists('value', $value)) {
                    $value['value'] = $name;
                }

                $this->valueMap[$name] = new EnumValueDefinition($value);
            }
        }

        return $this->valueMap;
    }

    /**
     * @param mixed $value
     *
     * @return string|null
     */
    public function coerce($value)
    {
        $enumValue = $this->getValueLookup()[$value];
        return $enumValue ? $enumValue->getName() : NULL;
    }

    /**
     * @param \Fubhy\GraphQL\Language\Node $value
     *
     * @return string|null
     */
    public function coerceLiteral(Node $value)
    {
        if ($value instanceof EnumValue) {
            $key = $value->get('value');
            if (($lookup = $this->getNameLookup()) && isset($lookup[$key])) {
                return $lookup[$key] ? $lookup[$key]->getName() : NULL;
            }
        }

        return NULL;
    }

    /**
     * @return \Fubhy\GraphQL\Type\Definition\EnumValueDefinition[]
     */
    protected function getValueLookup()
    {
        if (!isset($this->valueLookup)) {
            $this->valueLookup = [];
            foreach ($this->getValues() as $value) {
                $this->valueLookup[$value->getValue()] = $value;
            }
        }

        return $this->valueLookup;
    }

    /**
     * @return \Fubhy\GraphQL\Type\Definition\EnumValueDefinition[]
     */
    protected function getNameLookup()
    {
        if (!isset($this->nameLookup)) {
            $this->nameLookup = [];
            foreach ($this->getValues() as $value) {
                $this->nameLookup[$value->getName()] = $value;
            }
        }

        return $this->nameLookup;
    }
}
