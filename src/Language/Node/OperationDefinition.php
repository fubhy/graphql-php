<?php

namespace Fubhy\GraphQL\Language\Node;

use Fubhy\GraphQL\Language\Location;
use Fubhy\GraphQL\Language\Node;

class OperationDefinition extends Node implements DefinitionInterface
{
    const KIND = Node::KIND_OPERATION_DEFINITION;

    /**
     * Constructor.
     *
     * @param string $operation
     * @param \Fubhy\GraphQL\Language\Node\Name $name
     * @param \Fubhy\GraphQL\Language\Node\VariableDefinition[] $variableDefinitions
     * @param \Fubhy\GraphQL\Language\Node\Directive[] $directives
     * @param \Fubhy\GraphQL\Language\Node\SelectionSet $selectionSet
     * @param \Fubhy\GraphQL\Language\Location $location
     */
    public function __construct(
        $operation,
        Name $name = NULL,
        array $variableDefinitions = [],
        array $directives = [],
        SelectionSet $selectionSet,
        Location $location = NULL
    ) {
        parent::__construct($location, [
            'operation' => $operation,
            'name' => $name,
            'variableDefinitions' => $variableDefinitions,
            'directives' => $directives,
            'selectionSet' => $selectionSet,
        ]);
    }
}
