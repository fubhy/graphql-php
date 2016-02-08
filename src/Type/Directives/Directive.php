<?php

namespace Fubhy\GraphQL\Type\Directives;

/**
 * Directives are used by the GraphQL runtime as a way of modifying execution
 * behavior. Type system creators will usually not create these directly.
 */
abstract class Directive implements DirectiveInterface
{
    /**
     * @var \Fubhy\GraphQL\Type\Directives\DirectiveInterface
     */
    protected static $include;

    /**
     * @var \Fubhy\GraphQL\Type\Directives\DirectiveInterface
     */
    protected static $skip;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var array
     */
    protected $arguments;

    /**
     * @var \Fubhy\GraphQL\Type\Definition\Types\TypeInterface
     */
    protected $type;

    /**
     * @var bool
     */
    protected $onOperation;

    /**
     * @var bool
     */
    protected $onFragment;

    /**
     * @var bool
     */
    protected $onField;

    /**
     * @return \Fubhy\GraphQL\Type\Directives\DirectiveInterface
     */
    public static function includeDirective()
    {
        if (!isset(static::$include)) {
            static::$include = new IncludeDirective();
        }

        return static::$include;
    }

    /**
     * @return \Fubhy\GraphQL\Type\Directives\DirectiveInterface
     */
    public static function skipDirective()
    {
        if (!isset(static::$skip)) {
            static::$skip = new SkipDirective();
        }

        return static::$skip;
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
     * {@inheritdoc}
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function onOperation()
    {
        return $this->onOperation;
    }

    /**
     * {@inheritdoc}
     */
    public function onFragment()
    {
        return $this->onFragment;
    }

    /**
     * {@inheritdoc}
     */
    public function onField()
    {
        return $this->onField;
    }
}
