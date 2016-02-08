<?php

namespace Fubhy\GraphQL\Language\Node;

use Fubhy\GraphQL\Language\Location;
use Fubhy\GraphQL\Language\Node;

class StringValue extends Node implements ValueInterface
{
    const KIND = Node::KIND_STRING_VALUE;

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
