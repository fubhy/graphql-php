<?php

namespace Fubhy\GraphQL\Type\Definition\Types\Scalars;

use Fubhy\GraphQL\Language\Node;
use Fubhy\GraphQL\Language\Node\StringValue;
use Fubhy\GraphQL\Type\Definition\Types\ScalarType;

class StringType extends ScalarType
{
    /**
     * @var string
     */
    protected $name = 'String';

    /**
     * {@inheritdoc}
     */
    public function coerce($value)
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return (string) $value;
    }

    /**
     * {@inheritdoc}
     */
    public function coerceLiteral(Node $node)
    {
        return $node instanceof StringValue ? $node->get('value') : NULL;
    }
}
