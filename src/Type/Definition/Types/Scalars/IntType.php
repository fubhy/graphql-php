<?php

namespace Fubhy\GraphQL\Type\Definition\Types\Scalars;

use Fubhy\GraphQL\Language\Node;
use Fubhy\GraphQL\Language\Node\IntValue;
use Fubhy\GraphQL\Type\Definition\Types\ScalarType;

class IntType extends ScalarType
{
    /**
     * @var string
     */
    protected $name = 'Int';

    /**
     * {@inheritdoc}
     */
    public function coerce($value)
    {
        if ((is_numeric($value) && $value <= PHP_INT_MAX && $value >= -PHP_INT_MAX) || is_bool($value)) {
            return (int) $value;
        }

        return NULL;
    }

    /**
     * {@inheritdoc}
     */
    public function coerceLiteral(Node $node)
    {
        if ($node instanceof IntValue) {
            $num = $node->get('value');

            if ($num <= PHP_INT_MAX && $num >= -PHP_INT_MAX) {
                return intval($num);
            }
        }

        return NULL;
    }
}
