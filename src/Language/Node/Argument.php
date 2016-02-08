<?php

namespace Fubhy\GraphQL\Language\Node;

use Fubhy\GraphQL\Language\Location;
use Fubhy\GraphQL\Language\Node;

class Argument extends Node
{
    const KIND = Node::KIND_ARGUMENT;

    /**
     * Constructor.
     *
     * @param \Fubhy\GraphQL\Language\Node\Name $name
     * @param \Fubhy\GraphQL\Language\Node\ValueInterface $value
     * @param \Fubhy\GraphQL\Language\Location $location
     */
    public function __construct(Name $name, ValueInterface $value, Location $location = NULL)
    {
        parent::__construct($location, [
            'name' => $name,
            'value' => $value,
        ]);
    }
}
