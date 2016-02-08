<?php

namespace Fubhy\GraphQL\Tests\Executor;

use Fubhy\GraphQL\Executor\Executor;
use Fubhy\GraphQL\Language\Parser;
use Fubhy\GraphQL\Language\Source;
use Fubhy\GraphQL\Schema;
use Fubhy\GraphQL\Type\Definition\Types\ListModifier;
use Fubhy\GraphQL\Type\Definition\Types\ObjectType;
use Fubhy\GraphQL\Type\Definition\Types\Type;

class ExecutorTest extends \PHPUnit_Framework_TestCase
{
    public function testExecutesArbitraryCode()
    {
        $deepData = NULL;
        $data = [
            'a' => function () { return 'Apple';},
            'b' => function () {return 'Banana';},
            'c' => function () {return 'Cookie';},
            'd' => function () {return 'Donut';},
            'e' => function () {return 'Egg';},
            'f' => 'Fish',
            'pic' => function ($size = 50) {
                return 'Pic of size: ' . $size;
            },
            'promise' => function () use (&$data) {
                return $data;
            },
            'deep' => function () use (&$deepData) {
                return $deepData;
            }
        ];

        $deepData = [
            'a' => function () {
                return 'Already Been Done';
            },
            'b' => function () {
                return 'Boring';
            },
            'c' => function () {
                return ['Contrived', NULL, 'Confusing'];
            },
            'deeper' => function () use ($data) {
                return [$data, NULL, $data];
            }
        ];

        $document = '
            query Example($size: Int) {
                a,
                b,
                x: c
                ...c
                f
                ...on DataType {
                    pic(size: $size)
                    promise {
                        a
                    }
                }
                deep {
                    a
                    b
                    c
                    deeper {
                        a
                        b
                    }
                }
            }

            fragment c on DataType {
                d
                e
            }
        ';

        $parser = new Parser();
        $ast = $parser->parse(new Source($document));
        $expected = [
            'data' => [
                'a' => 'Apple',
                'b' => 'Banana',
                'x' => 'Cookie',
                'd' => 'Donut',
                'e' => 'Egg',
                'f' => 'Fish',
                'pic' => 'Pic of size: 100',
                'promise' => [
                    'a' => 'Apple'
                ],
                'deep' => [
                    'a' => 'Already Been Done',
                    'b' => 'Boring',
                    'c' => [ 'Contrived', NULL, 'Confusing' ],
                    'deeper' => [
                        [ 'a' => 'Apple', 'b' => 'Banana' ],
                        NULL,
                        [ 'a' => 'Apple', 'b' => 'Banana' ],
                    ],
                ],
            ],
        ];

        $deepDataType = NULL;
        $dataType = new ObjectType('DataType', [
            'a' => ['type' => Type::stringType()],
            'b' => ['type' => Type::stringType()],
            'c' => ['type' => Type::stringType()],
            'd' => ['type' => Type::stringType()],
            'e' => ['type' => Type::stringType()],
            'f' => ['type' => Type::stringType()],
            'pic' => [
                'args' => ['size' => ['type' => Type::intType()]],
                'type' => Type::stringType(),
                'resolve' => function($obj, $args) {
                    return $obj['pic']($args['size']);
                }
            ],
            'promise' => ['type' => function() use (&$dataType) {
                return $dataType;
            }],
            'deep' => [ 'type' => function() use(&$deepDataType) {
                return $deepDataType;
            }],
        ]);

        $deepDataType = new ObjectType('DeepDataType', [
            'a' => ['type' => Type::stringType()],
            'b' => ['type' => Type::stringType()],
            'c' => ['type' => new ListModifier(Type::stringType())],
            'deeper' => ['type' => new ListModifier($dataType)],
        ]);

        $schema = new Schema($dataType);

        $this->assertEquals($expected, Executor::execute($schema, $data, $ast, 'Example', ['size' => 100]));
    }

    public function testMergesParallelFragments()
    {
        $parser = new Parser();
        $ast = $parser->parse(new Source('
            { a, ...FragOne, ...FragTwo }

            fragment FragOne on Type {
                b
                deep { b, deeper: deep { b } }
            }

            fragment FragTwo on Type {
                c
                deep { c, deeper: deep { c } }
            }
        '));

        $type = new ObjectType('Type', [
            'a' => [
                'type' => Type::stringType(),
                'resolve' => function () {
                    return 'Apple';
                }
            ],
            'b' => [
                'type' => Type::stringType(),
                'resolve' => function () {
                    return 'Banana';
                }
            ],
            'c' => [
                'type' => Type::stringType(),
                'resolve' => function () {
                    return 'Cherry';
                }
            ],
            'deep' => [
                'type' => function () use (&$type) {
                    return $type;
                },
                'resolve' => function () {
                    return [];
                }
            ],
        ]);

        $schema = new Schema($type);
        $expected = [
            'data' => [
                'a' => 'Apple',
                'b' => 'Banana',
                'c' => 'Cherry',
                'deep' => [
                    'b' => 'Banana',
                    'c' => 'Cherry',
                    'deeper' => [
                        'b' => 'Banana',
                        'c' => 'Cherry',
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, Executor::execute($schema, NULL, $ast));
    }

    public function testThreadsContextCorrectly()
    {
        $document = 'query Example { a }';
        $gotHere = FALSE;
        $data = ['contextThing' => 'thing'];

        $parser = new Parser();
        $ast = $parser->parse(new Source($document));
        $schema = new Schema(new ObjectType('Type', [
            'a' => [
                'type' => Type::stringType(),
                'resolve' => function ($context) use ($document, &$gotHere) {
                    $this->assertEquals('thing', $context['contextThing']);
                    $gotHere = TRUE;
                }
            ],
        ]));

        Executor::execute($schema, $data, $ast, 'Example', []);
        $this->assertEquals(TRUE, $gotHere);
    }

    public function testCorrectlyThreadsArguments()
    {
        $document = '
            query Example {
                b(numArg: 123, stringArg: "foo")
            }
        ';

        $gotHere = FALSE;
        $parser = new Parser();
        $ast = $parser->parse(new Source($document));
        $schema = new Schema(new ObjectType('Type', [
            'b' => [
                'args' => [
                    'numArg' => ['type' => Type::intType()],
                    'stringArg' => ['type' => Type::stringType()]
                ],
                'type' => Type::stringType(),
                'resolve' => function ($_, $args) use (&$gotHere) {
                    $this->assertEquals(123, $args['numArg']);
                    $this->assertEquals('foo', $args['stringArg']);
                    $gotHere = TRUE;
                }
            ],
        ]));

        Executor::execute($schema, NULL, $ast, 'Example', []);
        $this->assertSame($gotHere, TRUE);
    }

    public function testNullsOutErrorSubtrees()
    {
        $document = '{
            sync,
            syncError,
            async,
            asyncReject,
            asyncError
        }';

        $data = [
            'sync' => function () {
                return 'sync';
            },
            'syncError' => function () {
                throw new \Exception('Error getting syncError.');
            },
            'async' => function() {
                return 'async';
            },
            'asyncReject' => function() {
                throw new \Exception('Error getting asyncReject.');
            },
            'asyncError' => function() {
                throw new \Exception('Error getting asyncError.');
            }
        ];

        $parser = new Parser();
        $ast = $parser->parse(new Source($document));
        $schema = new Schema(new ObjectType('Type', [
            'sync' => ['type' => Type::stringType()],
            'syncError' => ['type' => Type::stringType()],
            'async' => ['type' => Type::stringType()],
            'asyncReject' => ['type' => Type::stringType()],
            'asyncError' => ['type' => Type::stringType()],
        ]));

        $expected = [
            'data' => [
                'sync' => 'sync',
                'syncError' => NULL,
                'async' => 'async',
                'asyncReject' => NULL,
                'asyncError' => NULL,
            ],
            'errors' => [
                new \Exception('Error getting syncError.'),
                new \Exception('Error getting asyncReject.'),
                new \Exception('Error getting asyncError.'),
            ],
        ];

        $result = Executor::execute($schema, $data, $ast);
        $this->assertEquals($expected, $result);
    }

    public function testUsesTheInlineOperationIfNoOperationIsProvided()
    {
        $document = '{ a }';
        $data = ['a' => 'b'];
        $parser = new Parser();
        $ast = $parser->parse(new Source($document));
        $schema = new Schema(new ObjectType('Type', [
            'a' => ['type' => Type::stringType()],
        ]));

        $result = Executor::execute($schema, $data, $ast);
        $this->assertEquals(['data' => ['a' => 'b']], $result);
    }

    public function testUsesTheOnlyOperationIfNoOperationIsProvided()
    {
        $document = 'query Example { a }';
        $data = [ 'a' => 'b' ];
        $parser = new Parser();
        $ast = $parser->parse(new Source($document));
        $schema = new Schema(new ObjectType('Type', [
            'a' => ['type' => Type::stringType()],
        ]));

        $result = Executor::execute($schema, $data, $ast);
        $this->assertEquals(['data' => ['a' => 'b']], $result);
    }

    public function testThrowsIfNoOperationIsProvidedWithMultipleOperations()
    {
        $document = 'query Example { a } query OtherExample { a }';
        $data = [ 'a' => 'b' ];
        $parser = new Parser();
        $ast = $parser->parse(new Source($document));
        $schema = new Schema(new ObjectType('Type', [
            'a' => ['type' => Type::stringType()],
        ]));

        $result = Executor::execute($schema, $data, $ast);
        $error = new \Exception('Must provide operation name if query contains multiple operations');
        $this->assertEquals(['data' => NULL, 'errors' => [$error]], $result);
    }

    public function testUsesTheQuerySchemaForQueries()
    {
        $document = 'query Q { a } mutation M { c }';
        $data = ['a' => 'b', 'c' => 'd'];
        $parser = new Parser();
        $ast = $parser->parse(new Source($document));
        $schema = new Schema(
            new ObjectType('Q', [
                'a' => ['type' => Type::stringType()],
            ]),
            new ObjectType('M', [
                'c' => ['type' => Type::stringType()],
            ])
        );

        $result = Executor::execute($schema, $data, $ast, 'Q');
        $this->assertEquals(['data' => ['a' => 'b']], $result);
    }

    public function testUsesTheMutationSchemaForMutations()
    {
        $document = 'query Q { a } mutation M { c }';
        $data = [ 'a' => 'b', 'c' => 'd' ];
        $parser = new Parser();
        $ast = $parser->parse(new Source($document));
        $schema = new Schema(
            new ObjectType('Q', [
                'a' => ['type' => Type::stringType()],
            ]),
            new ObjectType('M', [
                'c' => [ 'type' => Type::stringType() ],
            ])
        );

        $result = Executor::execute($schema, $data, $ast, 'M');
        $this->assertEquals(['data' => ['c' => 'd']], $result);
    }

    public function testAvoidsRecursion()
    {
        $document = '
            query Q {
                a
                ...Frag
                ...Frag
            }

            fragment Frag on DataType {
                a,
                ...Frag
            }
        ';

        $data = ['a' => 'b'];
        $parser = new Parser();
        $ast = $parser->parse(new Source($document));
        $schema = new Schema(new ObjectType('Type', [
            'a' => ['type' => Type::stringType()],
        ]));

        $result = Executor::execute($schema, $data, $ast, 'Q');
        $this->assertEquals(['data' => ['a' => 'b']], $result);
    }

    public function testDoesNotIncludeIllegalFieldsInOutput()
    {
        $document = '
            mutation M {
                thisIsIllegalDontIncludeMe
            }
        ';

        $parser = new Parser();
        $ast = $parser->parse(new Source($document));
        $schema = new Schema(
            new ObjectType('Q', [
                'a' => ['type' => Type::stringType()],
            ]),
            new ObjectType('M', [
                'c' => ['type' => Type::stringType()],
            ])
        );

        $result = Executor::execute($schema, NULL, $ast);
        $this->assertEquals(['data' => []], $result);
    }
}
