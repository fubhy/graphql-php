<?php

namespace Fubhy\GraphQL\Type\Definition\Types;

/**
 * Scalar Type Definition.
 *
 * The leaf values of any request and input values to arguments are Scalars (or
 * Enums) and are defined with a name and a series of coercion functions used to
 * ensure validity.
 */
abstract class ScalarType extends Type implements ScalarTypeInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $description;

    /**
     * Constructor.
     */
    public function __construct()
    {
        // Override the parent implementation.
    }
}
