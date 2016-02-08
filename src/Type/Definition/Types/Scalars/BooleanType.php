<?php

namespace Fubhy\GraphQL\Type\Definition\Types\Scalars;

use Fubhy\GraphQL\Language\Node;
use Fubhy\GraphQL\Language\Node\BooleanValue;
use Fubhy\GraphQL\Type\Definition\Types\ScalarType;

class BooleanType extends ScalarType
{
    /**
     * @var string
     */
    protected $name = 'Boolean';

    /**
     * {@inheritdoc}
     */
    public function coerce($value)
    {
        return (bool) $value;
    }

    /**
     * {@inheritdoc}
     */
    public function coerceLiteral(Node $node)
    {
        return $node instanceof BooleanValue ? $node->get('value') : NULL;
    }
}
