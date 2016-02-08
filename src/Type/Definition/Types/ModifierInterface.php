<?php

namespace Fubhy\GraphQL\Type\Definition\Types;

/**
 * These types are modifiers like ListModifier or NonNullModifier.
 */
interface ModifierInterface extends TypeInterface
{
    /**
     * @return \Fubhy\GraphQL\Type\Definition\Types\TypeInterface
     */
    public function getWrappedType();
}
