<?php

namespace Fubhy\GraphQL\Language\Node;

use Fubhy\GraphQL\Language\Location;
use Fubhy\GraphQL\Language\Node;

class NamedType extends Node implements TypeInterface
{
    const KIND = Node::KIND_NAMED_TYPE;

    /**
     * Constructor.
     *
     * @param string $name
     * @param \Fubhy\GraphQL\Language\Location $location
     */
    public function __construct($name, Location $location = NULL)
    {
        parent::__construct($location, ['name' => $name]);
    }
}
