<?php

namespace Fubhy\GraphQL\Type\Definition\Types;

use Fubhy\GraphQL\Type\Definition\Types\Scalars\BooleanType;
use Fubhy\GraphQL\Type\Definition\Types\Scalars\FloatType;
use Fubhy\GraphQL\Type\Definition\Types\Scalars\IdType;
use Fubhy\GraphQL\Type\Definition\Types\Scalars\IntType;
use Fubhy\GraphQL\Type\Definition\Types\Scalars\StringType;

/**
 * These are all of the possible kinds of types.
 */
abstract class Type implements TypeInterface
{
    /**
     * @var \Fubhy\GraphQL\Type\Definition\Types\Scalars\BooleanType
     */
    protected static $boolean;

    /**
     * @var \Fubhy\GraphQL\Type\Definition\Types\Scalars\FloatType
     */
    protected static $float;

    /**
     * @var \Fubhy\GraphQL\Type\Definition\Types\Scalars\IdType
     */
    protected static $id;

    /**
     * @var \Fubhy\GraphQL\Type\Definition\Types\Scalars\IntType
     */
    protected static $integer;

    /**
     * @var \Fubhy\GraphQL\Type\Definition\Types\Scalars\StringType
     */
    protected static $string;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $description;

    /**
     * @return \Fubhy\GraphQL\Type\Definition\Types\Scalars\BooleanType
     */
    public static function booleanType()
    {
        if (!isset(static::$boolean)) {
            static::$boolean = new BooleanType();
        }

        return static::$boolean;
    }

    /**
     * @return \Fubhy\GraphQL\Type\Definition\Types\Scalars\FloatType
     */
    public static function floatType()
    {
        if (!isset(static::$float)) {
            static::$float = new FloatType();
        }

        return static::$float;
    }

    /**
     * @return \Fubhy\GraphQL\Type\Definition\Types\Scalars\IdType
     */
    public static function idType()
    {
        if (!isset(static::$id)) {
            static::$id = new IdType();
        }

        return static::$id;
    }

    /**
     * @return \Fubhy\GraphQL\Type\Definition\Types\Scalars\IntType
     */
    public static function intType()
    {
        if (!isset(static::$integer)) {
            static::$integer = new IntType();
        }

        return static::$integer;
    }

    /**
     * @return \Fubhy\GraphQL\Type\Definition\Types\Scalars\StringType
     */
    public static function stringType()
    {
        if (!isset(static::$string)) {
            static::$string = new StringType();
        }

        return static::$string;
    }

    /**
     * Constructor.
     *
     * @param string $name
     * @param string|null $description
     */
    public function __construct($name, $description = NULL)
    {
        $this->name = $name;
        $this->description = $description;
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
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function isInputType()
    {
        return $this->getUnmodifiedType() instanceof InputTypeInterface;
    }

    /**
     * {@inheritdoc}
     */
    public function isOutputType()
    {
        return $this->getUnmodifiedType() instanceof OutputTypeInterface;
    }

    /**
     * {@inheritdoc}
     */
    public function isLeafType()
    {
        return $this->getUnmodifiedType() instanceof LeafTypeInterface;
    }

    /**
     * {@inheritdoc}
     */
    public function isCompositeType()
    {
        return $this->getUnmodifiedType() instanceof CompositeTypeInterface;
    }

    /**
     * {@inheritdoc}
     */
    public function isAbstractType()
    {
        return $this->getUnmodifiedType() instanceof AbstractTypeInterface;
    }

    /**
     * {@inheritdoc}
     */
    public function getNullableType()
    {
        if ($this instanceof NonNullModifier) {
            return $this->getWrappedType();
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUnmodifiedType()
    {
        $return = $this;
        while ($return instanceof ListModifier || $return instanceof NonNullModifier) {
            $return = $return->getWrappedType();
        }

        return $return;
    }
}
