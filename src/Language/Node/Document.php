<?php

namespace Fubhy\GraphQL\Language\Node;

use Fubhy\GraphQL\Language\Location;
use Fubhy\GraphQL\Language\Node;

class Document extends Node
{
    const KIND = Node::KIND_DOCUMENT;
    /**
     * Constructor.
     *
     * @param \Fubhy\GraphQL\Language\Location $location
     * @param \Fubhy\GraphQL\Language\Node\DefinitionInterface[] $definitions
     */
    public function __construct(array $definitions, Location $location = NULL)
    {
        parent::__construct($location, ['definitions' => $definitions]);
    }
}
