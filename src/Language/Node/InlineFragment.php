<?php

namespace Fubhy\GraphQL\Language\Node;

use Fubhy\GraphQL\Language\Location;
use Fubhy\GraphQL\Language\Node;

class InlineFragment extends Node implements SelectionInterface
{
    const KIND = Node::KIND_INLINE_FRAGMENT;

    /**
     * Constructor.
     *
     * @param \Fubhy\GraphQL\Language\Node\NamedType $typeCondition
     * @param \Fubhy\GraphQL\Language\Node\Directive[] $directives
     * @param \Fubhy\GraphQL\Language\Node\SelectionSet $selectionSet
     * @param \Fubhy\GraphQL\Language\Location $location
     */
    public function __construct(
        NamedType $typeCondition,
        array $directives = [],
        SelectionSet $selectionSet,
        Location $location = NULL
    ) {
        parent::__construct($location, [
            'typeCondition' => $typeCondition,
            'directives' => $directives,
            'selectionSet' => $selectionSet,
        ]);
    }
}
