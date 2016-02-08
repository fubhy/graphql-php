<?php

namespace Fubhy\GraphQL\Type\Definition\Types;

/**
 * These are all of the possible kinds of types.
 */
interface TypeInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return string|null
     */
    public function getDescription();

    /**
     * @return bool
     */
    public function isInputType();

    /**
     * @return bool
     */
    public function isOutputType();

    /**
     * @return bool
     */
    public function isLeafType();

    /**
     * @return bool
     */
    public function isCompositeType();

    /**
     * @return bool
     */
    public function isAbstractType();

    /**
     * @return \Fubhy\GraphQL\Type\Definition\Types\TypeInterface
     */
    public function getNullableType();

    /**
     * @return \Fubhy\GraphQL\Type\Definition\Types\TypeInterface
     */
    public function getUnmodifiedType();

    /**
     * @return string
     */
    public function __toString();
}
