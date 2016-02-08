<?php

namespace Fubhy\GraphQL\Language\Node;

use Fubhy\GraphQL\Language\Location;
use Fubhy\GraphQL\Language\Node;

class Variable extends Node implements ValueInterface
{
    const KIND = Node::KIND_VARIABLE;

    /**
     * Constructor.
     *
     * @param \Fubhy\GraphQL\Language\Node\Name $name
     * @param \Fubhy\GraphQL\Language\Location $location
     */
    public function __construct(Name $name = NULL, Location $location = NULL)
    {
        parent::__construct($location, ['name' => $name]);
    }
}
