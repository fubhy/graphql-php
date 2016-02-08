<?php

namespace Fubhy\GraphQL\Type\Definition\Types\Scalars;

use Fubhy\GraphQL\Language\Node;
use Fubhy\GraphQL\Language\Node\FloatValue;
use Fubhy\GraphQL\Language\Node\IntValue;
use Fubhy\GraphQL\Type\Definition\Types\ScalarType;

class FloatType extends ScalarType
{
    /**
     * @var string
     */
    protected $name = 'Float';

    /**
     * {@inheritdoc}
     */
    public function coerce($value)
    {
        return is_numeric($value) || is_bool($value) ? (float) $value : NULL;
    }

    /**
     * {@inheritdoc}
     */
    public function coerceLiteral(Node $node)
    {
        if ($node instanceof IntValue || $node instanceof FloatValue) {
            $num = (float) $node->get('value');

            if ($num <= PHP_INT_MAX && $num >= -PHP_INT_MAX) {
                return $num;
            }
        }

        return NULL;
    }
}
