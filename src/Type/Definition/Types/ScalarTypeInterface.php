<?php

namespace Fubhy\GraphQL\Type\Definition\Types;

use Fubhy\GraphQL\Language\Node;

/**
 * Scalar Type Definition.
 *
 * The leaf values of any request and input values to arguments are
 * Scalars (or Enums) and are defined with a name and a series of coercion
 * functions used to ensure validity.
 */
interface ScalarTypeInterface extends
    OutputTypeInterface,
    InputTypeInterface,
    LeafTypeInterface,
    NullableTypeInterface,
    UnmodifiedTypeInterface
{
    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function coerce($value);

    /**
     * @param \Fubhy\GraphQL\Language\Node $value
     *
     * @return mixed
     */
    public function coerceLiteral(Node $value);
}
