<?php

namespace Fubhy\GraphQL\Language\Node;

use Fubhy\GraphQL\Language\Location;
use Fubhy\GraphQL\Language\Node;

class ObjectValue extends Node implements ValueInterface
{
    const KIND = Node::KIND_OBJECT_VALUE;

    /**
     * Constructor.
     *
     * @param \Fubhy\GraphQL\Language\Node\ObjectField[] $fields
     * @param \Fubhy\GraphQL\Language\Location $location
     */
    public function __construct(array $fields, Location $location = NULL)
    {
        parent::__construct($location, ['fields' => $fields]);
    }
}
