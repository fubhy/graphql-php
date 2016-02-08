<?php

namespace Fubhy\GraphQL\Type\Definition\Types;

/**
 * These types may describe the parent context of a selection set.
 */
interface AbstractTypeInterface extends TypeInterface
{
    /**
     * @return \Fubhy\GraphQL\Type\Definition\Types\ObjectType[] $type
     */
    public function getPossibleTypes();

    /**
     * @param \Fubhy\GraphQL\Type\Definition\Types\ObjectType $type
     */
    public function addPossibleType(ObjectType $type);

    /**
     * @param \Fubhy\GraphQL\Type\Definition\Types\ObjectType $type
     *
     * @return bool
     */
    public function isPossibleType(ObjectType $type);

    /**
     * @param mixed $value
     *
     * @return \Fubhy\GraphQL\Type\Definition\Types\TypeInterface
     */
    public function resolveType($value);

    /**
     * @param mixed $value
     *
     * @return \Fubhy\GraphQL\Type\Definition\Types\TypeInterface
     */
    public function getTypeOf($value);
}
