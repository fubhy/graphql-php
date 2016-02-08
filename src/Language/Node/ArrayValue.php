<?php

namespace Fubhy\GraphQL\Language\Node;

use Fubhy\GraphQL\Language\Location;
use Fubhy\GraphQL\Language\Node;

class ArrayValue extends Node implements ValueInterface
{
    const KIND = Node::KIND_ARRAY_VALUE;

    /**
     * Constructor.
     *
     * @param \Fubhy\GraphQL\Language\Node\ValueInterface[] $values
     * @param \Fubhy\GraphQL\Language\Location $location
     */
    public function __construct(array $values, Location $location = NULL)
    {
        parent::__construct($location, ['values' => $values]);
    }
}
