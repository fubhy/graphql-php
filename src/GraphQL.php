<?php

namespace Fubhy\GraphQL;

use Fubhy\GraphQL\Executor\Executor;
use Fubhy\GraphQL\Language\Parser;
use Fubhy\GraphQL\Language\Source;

class GraphQL
{
    /**
     * @param Schema $schema
     * @param $request
     * @param mixed $root
     * @param array|null $variables
     * @param string|null $operation
     *
     * @return array
     */
    public static function execute(Schema $schema, $request, $root = NULL, $variables = NULL, $operation = NULL)
    {
        try {
            $source = new Source($request ?: '', 'GraphQL request');
            $parser = new Parser();

            $ast = $parser->parse($source);
            return Executor::execute($schema, $root, $ast, $operation, $variables);
        } catch (\Exception $exception) {
            return ['errors' => $exception->getMessage()];
        }
    }
}
