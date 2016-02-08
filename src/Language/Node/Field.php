<?php

namespace Fubhy\GraphQL\Language\Node;

use Fubhy\GraphQL\Language\Location;
use Fubhy\GraphQL\Language\Node;

class Field extends Node implements SelectionInterface
{
    const KIND = Node::KIND_FIELD;

    /**
     * Constructor.
     *
     * @param \Fubhy\GraphQL\Language\Node\Name $name
     * @param \Fubhy\GraphQL\Language\Node\Name $alias
     * @param \Fubhy\GraphQL\Language\Node\Argument[] $arguments
     * @param \Fubhy\GraphQL\Language\Node\Directive[] $directives
     * @param \Fubhy\GraphQL\Language\Node\SelectionSet $selectionSet
     * @param \Fubhy\GraphQL\Language\Location $location
     */
    public function __construct(
        Name $name,
        Name $alias = NULL,
        array $arguments = [],
        array $directives = [],
        SelectionSet $selectionSet = NULL,
        Location $location = NULL
    ) {
        parent::__construct($location, [
            'name' => $name,
            'alias' => $alias,
            'arguments' => $arguments,
            'directives' => $directives,
            'selectionSet' => $selectionSet,
        ]);
    }
}
