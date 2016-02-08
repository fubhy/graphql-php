<?php

namespace Fubhy\GraphQL\Language\Node;

use Fubhy\GraphQL\Language\Location;
use Fubhy\GraphQL\Language\Node;

class BooleanValue extends Node implements ValueInterface
{
    const KIND = Node::KIND_BOOLEAN_VALUE;

    /**
     * Constructor.
     *
     * @param bool $value
     * @param \Fubhy\GraphQL\Language\Location $location
     */
    public function __construct($value, Location $location = NULL)
    {
        parent::__construct($location, ['value' => $value]);
    }
}
