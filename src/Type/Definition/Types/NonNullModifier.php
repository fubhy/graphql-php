<?php

namespace Fubhy\GraphQL\Type\Definition\Types;

/**
 * Non-Null Modifier.
 *
 * A non-null is a kind of type marker, a wrapping type which points to another
 * type. Non-null types enforce that their values are never null and can ensure
 * an error is raised if this ever occurs during a request. It is useful for
 * fields which you can make a strong guarantee on non-nullability, for example
 * usually the id field of a database row will never be null.
 *
 * Note: The enforcement of non-nullability occurs within the executor.
 */
class NonNullModifier extends Type implements TypeInterface, InputTypeInterface, OutputTypeInterface, ModifierInterface
{
    /**
     * @var callable|\Fubhy\GraphQL\Type\Definition\Types\TypeInterface
     */
    protected $ofType;

    /**
     * Constructor.
     *
     * @param callable|\Fubhy\GraphQL\Type\Definition\Types\TypeInterface $ofType
     */
    public function __construct($ofType)
    {
        if (!($ofType instanceof TypeInterface) && !is_callable($ofType)) {
            throw new \LogicException('Expected callable or instance of \Fubhy\GraphQL\Type\Definition\Types\TypeInterface.');
        }

        $this->ofType = $ofType;
    }

    /**
     * @return \Fubhy\GraphQL\Type\Definition\Types\TypeInterface
     */
    public function getWrappedType()
    {
        if (is_callable($this->ofType)) {
            return call_user_func($this->ofType);
        }

        return $this->ofType;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return (string) $this->getWrappedType() . '!';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return NULL;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->getName();
    }
}
