<?php

namespace Fubhy\GraphQL\Type\Definition\Types\Scalars;

use Fubhy\GraphQL\Language\Node;
use Fubhy\GraphQL\Language\Node\IntValue;
use Fubhy\GraphQL\Language\Node\StringValue;
use Fubhy\GraphQL\Type\Definition\Types\ScalarType;

class IdType extends ScalarType
{
    /**
     * @var string
     */
    protected $name = 'Id';

    /**
     * {@inheritdoc}
     */
    public function coerce($value)
    {
        return (string) $value;
    }

    /**
     * {@inheritdoc}
     */
    public function coerceLiteral(Node $node)
    {
        if ($node instanceof StringValue || $node instanceof IntValue) {
            return $node->get('value');
        }

        return NULL;
    }
}
