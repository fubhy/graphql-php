<?php

namespace Fubhy\GraphQL\Type\Definition;

class InputObjectField
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var \Fubhy\GraphQL\Type\Definition\Types\InputTypeInterface|callable
     */
    protected $type;

    /**
     * @var mixed|null
     */
    protected $defaultValue;

    /**
     * @var string|null
     */
    protected $description;

    /**
     * Constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->name = $config['name'];
        $this->type = $config['type'];
        $this->description = isset($config['description']) ? $config['description'] : NULL;
        $this->defaultValue = isset($config['defaultValue']) ? $config['defaultValue'] : NULL;
    }

    /**
     * @return \Fubhy\GraphQL\Type\Definition\Types\InputTypeInterface
     */
    public function getType()
    {
        if (is_callable($this->type)) {
            return call_user_func($this->type);
        }

        return $this->type;
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
     * @return mixed|null
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }
}
