<?php

namespace Fubhy\GraphQL\Language\Node;

use Fubhy\GraphQL\Language\Location;
use Fubhy\GraphQL\Language\Node;

class FragmentSpread extends Node implements SelectionInterface
{
    const KIND = Node::KIND_FRAGMENT_SPREAD;

    /**
     * Constructor.
     *
     * @param \Fubhy\GraphQL\Language\Node\Name $name
     * @param \Fubhy\GraphQL\Language\Node\Directive[] $directives
     * @param \Fubhy\GraphQL\Language\Location $location
     */
    public function __construct(Name $name, array $directives = [], Location $location = NULL)
    {
        parent::__construct($location, [
            'name' => $name,
            'directives' => $directives,
        ]);
    }
}
