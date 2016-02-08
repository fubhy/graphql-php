<?php

namespace Fubhy\GraphQL\Executor;

use Fubhy\GraphQL\Language\Node\OperationDefinition;
use Fubhy\GraphQL\Schema;

/**
 * Data that must be available at all points during query execution.
 *
 * Namely, schema of the type system that is currently executing,
 * and the fragments defined in the query document
 */
class ExecutionContext
{
    /**
     * @var \Fubhy\GraphQL\Schema
     */
    public $schema;

    /**
     * @var array
     */
    public $fragments;

    /**
     * @var array
     */
    public $root;

    /**
     * @var \Fubhy\GraphQL\Language\Node\OperationDefinition
     */
    public $operation;

    /**
     * @var array
     */
    public $variables;

    /**
     * @var array
     */
    public $errors;

    /**
     * Constructor.
     *
     * @param $schema
     * @param $fragments
     * @param $root
     * @param $operation
     * @param $variables
     * @param $errors
     */
    public function __construct($schema, $fragments, $root, $operation, $variables, $errors)
    {
        $this->schema = $schema;
        $this->fragments = $fragments;
        $this->root = $root;
        $this->operation = $operation;
        $this->variables = $variables;
        $this->errors = $errors ?: [];
    }
}
