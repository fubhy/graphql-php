<?php

namespace Fubhy\GraphQL\Language\Node;

use Fubhy\GraphQL\Language\Location;
use Fubhy\GraphQL\Language\Node;

class FragmentDefinition extends Node implements DefinitionInterface
{
    const KIND = Node::KIND_FRAGMENT_DEFINITION;

    /**
     * Constructor.
     *
     * @param \Fubhy\GraphQL\Language\Node\Name $name
     * @param \Fubhy\GraphQL\Language\Node\NamedType $typeCondition
     * @param \Fubhy\GraphQL\Language\Node\Directive[] $directives
     * @param \Fubhy\GraphQL\Language\Node\SelectionSet $selectionSet
     * @param \Fubhy\GraphQL\Language\Location $location
     */
    public function __construct(
        Name $name,
        NamedType $typeCondition,
        array $directives = [],
        SelectionSet $selectionSet,
        Location $location = NULL
    ) {
        parent::__construct($location, [
            'name' => $name,
            'typeCondition' => $typeCondition,
            'directives' => $directives,
            'selectionSet' => $selectionSet,
        ]);
    }
}
