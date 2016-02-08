<?php

namespace Fubhy\GraphQL\Language\Node;

use Fubhy\GraphQL\Language\Location;
use Fubhy\GraphQL\Language\Node;

class ListType extends Node implements TypeInterface
{
    const KIND = Node::KIND_LIST_TYPE;

    /**
     * Constructor.
     *
     * @param \Fubhy\GraphQL\Language\Node\TypeInterface $type
     * @param \Fubhy\GraphQL\Language\Location $location
     */
    public function __construct(TypeInterface $type, Location $location = NULL)
    {
        parent::__construct($location, ['type' => $type]);
    }
}
