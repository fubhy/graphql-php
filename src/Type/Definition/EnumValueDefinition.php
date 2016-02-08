<?php

namespace Fubhy\GraphQL\Type\Definition;

class EnumValueDefinition
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string|null
     */
    protected $description;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * @var string|null
     */
    protected $deprecationReason;

    /**
     * Constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->name = $config['name'];
        $this->value = $config['value'];
        $this->description = isset($config['description']) ? $config['description'] : NULL;

        if (isset($config['deprecationReason'])) {
            $this->deprecationReason = $config['deprecationReason'];
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string|null
     */
    public function getDeprecationReason()
    {
        return $this->deprecationReason;
    }
}
