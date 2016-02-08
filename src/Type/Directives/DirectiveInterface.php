<?php

namespace Fubhy\GraphQL\Type\Directives;

/**
 * Directives are used by the GraphQL runtime as a way of modifying execution
 * behavior. Type system creators will usually not create these directly.
 */
interface DirectiveInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @return array
     */
    public function getArguments();

    /**
     * @return \Fubhy\GraphQL\Type\Definition\Types\TypeInterface
     */
    public function getType();

    /**
     * @return bool
     */
    public function onOperation();

    /**
     * @return bool
     */
    public function onFragment();

    /**
     * @return bool
     */
    public function onField();
}
