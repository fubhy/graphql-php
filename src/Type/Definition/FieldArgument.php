<?php

namespace Fubhy\GraphQL\Type\Definition;

class FieldArgument
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
     * @var \Fubhy\GraphQL\Type\Definition\Types\InputTypeInterface|callable
     */
    protected $type;

    /**
     * @var mixed
     */
    protected $defaultValue;

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

        if (isset($config['defaultValue'])) {
            $this->defaultValue = $config['defaultValue'];
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
     * @return mixed|null
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }
}
