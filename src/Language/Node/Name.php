<?php

namespace Fubhy\GraphQL\Language\Node;

use Fubhy\GraphQL\Language\Location;
use Fubhy\GraphQL\Language\Node;

class Name extends Node implements TypeInterface
{
    const KIND = Node::KIND_NAME;

    /**
     * Constructor.
     *
     * @param string $value
     * @param \Fubhy\GraphQL\Language\Location $location
     */
    public function __construct($value, Location $location = NULL)
    {
        parent::__construct($location, ['value' => $value]);
    }
}
