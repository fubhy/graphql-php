<?php

namespace Fubhy\GraphQL\Tests\Executor;

use Fubhy\GraphQL\Executor\Executor;
use Fubhy\GraphQL\Language\Source;
use Fubhy\GraphQL\Type\Definition\Types\NonNullModifier;
use Fubhy\GraphQL\Language\Parser;
use Fubhy\GraphQL\Schema;
use Fubhy\GraphQL\Type\Definition\Types\ObjectType;
use Fubhy\GraphQL\Type\Definition\Types\Type;

class NonNullTest extends \PHPUnit_Framework_TestCase
{
    protected $syncError;
    protected $nonNullSyncError;
    protected $throwingData;
    protected $NULLingData;
    protected $schema;

    protected function setUp()
    {
        $this->syncError = new \Exception('sync');
        $this->nonNullSyncError = new \Exception('nonNullSync');

        $this->throwingData = [
            'sync' => function () {
                throw $this->syncError;
            },
            'nonNullSync' => function () {
                throw $this->nonNullSyncError;
            },
            'nest' => function () {
                return $this->throwingData;
            },
            'nonNullNest' => function () {
                return $this->throwingData;
            },
        ];

        $this->NULLingData = [
            'sync' => function () {
                return NULL;
            },
            'nonNullSync' => function () {
                return NULL;
            },
            'nest' => function () {
                return $this->NULLingData;
            },
            'nonNullNest' => function () {
                return $this->NULLingData;
            },
        ];

        $dataType = new ObjectType('DataType', [
            'sync' => ['type' => Type::stringType()],
            'nonNullSync' => ['type' => new NonNullModifier(Type::stringType())],
            'nest' => ['type' => function () use (&$dataType) {
                return $dataType;
            }],
            'nonNullNest' => ['type' => function () use (&$dataType) {
                return new NonNullModifier($dataType);
            }]
        ]);

        $this->schema = new Schema($dataType);
    }

    public function testNullsANullableFieldThatThrowsSynchronously()
    {
        $document = '
            query Q {
                sync
            }
        ';

        $parser = new Parser();
        $ast = $parser->parse(new Source($document));
        $expected = [
            'data' => ['sync' => NULL],
            'errors' => [new \Exception($this->syncError->getMessage())],
        ];

        $this->assertEquals($expected, Executor::execute($this->schema, $this->throwingData, $ast, 'Q', []));
    }

    public function testNullsASynchronouslyReturnedObjectThatContainsANonNullableFieldThatThrowsSynchronously()
    {
        $document = '
            query Q {
                nest {
                    nonNullSync,
                }
            }
        ';

        $parser = new Parser();
        $ast = $parser->parse(new Source($document));
        $expected = [
            'data' => ['nest' => NULL],
            'errors' => [new \Exception($this->nonNullSyncError->getMessage())],
        ];

        $this->assertEquals($expected, Executor::execute($this->schema, $this->throwingData, $ast, 'Q', []));
    }

    public function testNullsAComplexTreeOfNullableFieldsThatThrow()
    {
        $document = '
            query Q {
                nest {
                    sync
                    nest {
                        sync
                    }
                }
            }
        ';

        $parser = new Parser();
        $ast = $parser->parse(new Source($document));
        $expected = [
            'data' => [
                'nest' => [
                    'sync' => NULL,
                    'nest' => ['sync' => NULL],
                ],
            ],
            'errors' => [
                new \Exception($this->syncError->getMessage()),
                new \Exception($this->syncError->getMessage()),
            ],
        ];

        $this->assertEquals($expected, Executor::execute($this->schema, $this->throwingData, $ast, 'Q', []));
    }

    public function testNullsANullableFieldThatSynchronouslyReturnsNull()
    {
        $document = '
            query Q {
                sync
            }
        ';

        $parser = new Parser();
        $ast = $parser->parse(new Source($document));
        $expected = ['data' => ['sync' => NULL]];

        $this->assertEquals($expected, Executor::execute($this->schema, $this->NULLingData, $ast, 'Q', []));
    }

    public function test4()
    {
        $document = '
            query Q {
                nest {
                    nonNullSync,
                }
            }
        ';

        $parser = new Parser();
        $ast = $parser->parse(new Source($document));
        $expected = [
            'data' => ['nest' => NULL],
            'errors' => [new \Exception('Cannot return null for non-nullable type.')],
        ];

        $this->assertEquals($expected, Executor::execute($this->schema, $this->NULLingData, $ast, 'Q', []));
    }

    public function test5()
    {
        $document = '
            query Q {
                nest {
                    sync
                    nest {
                        sync
                        nest {
                            sync
                        }
                    }
                }
            }
        ';

        $parser = new Parser();
        $ast = $parser->parse(new Source($document));
        $expected = [
            'data' => [
                'nest' => [
                    'sync' => NULL,
                    'nest' => [
                        'sync' => NULL,
                        'nest' => ['sync' => NULL],
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, Executor::execute($this->schema, $this->NULLingData, $ast, 'Q', []));
    }

    public function testNullsTheTopLevelIfSyncNonNullableFieldThrows()
    {
        $document = 'query Q { nonNullSync }';

        $expected = [
            'data' => NULL,
            'errors' => [new \Exception($this->nonNullSyncError->getMessage())],
        ];

        $parser = new Parser();
        $ast = $parser->parse(new Source($document));
        $this->assertEquals($expected, Executor::execute($this->schema, $this->throwingData, $ast));
    }

    public function testNullsTheTopLevelIfSyncNonNullableFieldReturnsNull()
    {
        $document = 'query Q { nonNullSync }';
        $expected = [
            'data' => NULL,
            'errors' => [new \Exception('Cannot return null for non-nullable type.')],
        ];

        $parser = new Parser();
        $ast = $parser->parse(new Source($document));
        $this->assertEquals($expected, Executor::execute($this->schema, $this->NULLingData, $ast));
    }
}
