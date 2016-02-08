<?php

namespace Fubhy\GraphQL\Language\Node;

use Fubhy\GraphQL\Language\Location;
use Fubhy\GraphQL\Language\Node;

class SelectionSet extends Node
{
    const KIND = Node::KIND_SELECTION_SET;

    /**
     * Constructor.
     *
     * @param \Fubhy\GraphQL\Language\Node\SelectionInterface[] $selections
     * @param \Fubhy\GraphQL\Language\Location $location
     */
    public function __construct(array $selections, Location $location = NULL)
    {
        parent::__construct($location, ['selections' => $selections]);
    }
}
