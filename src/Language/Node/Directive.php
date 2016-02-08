<?php

namespace Fubhy\GraphQL\Language\Node;

use Fubhy\GraphQL\Language\Location;
use Fubhy\GraphQL\Language\Node;

class Directive extends Node
{
    const KIND = Node::KIND_DIRECTIVE;

    /**
     * Constructor.
     *
     * @param \Fubhy\GraphQL\Language\Node\Name $name
     * @param \Fubhy\GraphQL\Language\Node\Argument[] $arguments
     * @param \Fubhy\GraphQL\Language\Location $location
     */
    public function __construct(Name $name, array $arguments = NULL, Location $location = NULL)
    {
        parent::__construct($location, [
            'name' => $name,
            'arguments' => $arguments,
        ]);
    }
}
