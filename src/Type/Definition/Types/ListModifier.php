<?php

namespace Fubhy\GraphQL\Type\Definition\Types;

/**
 * List Modifier.
 *
 * A list is a kind of type marker, a wrapping type which points to another
 * type. Lists are often created within the context of defining the fields of an
 * object type.
 */
class ListModifier extends Type implements TypeInterface, InputTypeInterface, OutputTypeInterface, NullableTypeInterface, ModifierInterface
{
    /**
     * @var callable|\Fubhy\GraphQL\Type\Definition\Types\TypeInterface
     */
    protected $ofType;

    /**
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
            $this->ofType = call_user_func($this->ofType);
        }

        return $this->ofType;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return '[' . (string) $this->getWrappedType() . ']';
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
