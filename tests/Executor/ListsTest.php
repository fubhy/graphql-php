<?php

namespace Fubhy\GraphQL\Tests\Executor;

use Fubhy\GraphQL\Executor\Executor;
use Fubhy\GraphQL\Language\Parser;
use Fubhy\GraphQL\Language\Source;
use Fubhy\GraphQL\Schema;
use Fubhy\GraphQL\Type\Definition\Types\ListModifier;
use Fubhy\GraphQL\Type\Definition\Types\NonNullModifier;
use Fubhy\GraphQL\Type\Definition\Types\ObjectType;
use Fubhy\GraphQL\Type\Definition\Types\Type;

class ListsTest extends \PHPUnit_Framework_TestCase
{
    public function testHandlesListsWhenTheyReturnNonNullValues()
    {
        $document = '
            query Q {
                nest {
                    list,
                }
            }
        ';

        $parser = new Parser();
        $ast = $parser->parse(new Source($document));
        $expected = ['data' => ['nest' => ['list' => [1,2]]]];

        $this->assertEquals($expected, Executor::execute($this->getSchema(), $this->getData(), $ast, 'Q', []));
    }

    public function testHandlesListsOfNonNullsWhenTheyReturnNonNullValues()
    {
        $document = '
            query Q {
                nest {
                    listOfNonNull,
                }
            }
        ';

        $parser = new Parser();
        $ast = $parser->parse(new Source($document));
        $expected = ['data' => ['nest' => ['listOfNonNull' => [1, 2]]]];

        $this->assertEquals($expected, Executor::execute($this->getSchema(), $this->getData(), $ast, 'Q', []));
    }

    public function testHandlesNonNullListsOfWhenTheyReturnNonNullValues()
    {
        $document = '
            query Q {
                nest {
                    nonNullList,
                }
            }
        ';

        $parser = new Parser();
        $ast = $parser->parse(new Source($document));
        $expected = ['data' => ['nest' => ['nonNullList' => [1, 2]]]];

        $this->assertEquals($expected, Executor::execute($this->getSchema(), $this->getData(), $ast, 'Q', []));
    }

    public function testHandlesNonNullListsOfNonNullsWhenTheyReturnNonNullValues()
    {
        $document = '
            query Q {
                nest {
                    nonNullListOfNonNull,
                }
            }
        ';

        $parser = new Parser();
        $ast = $parser->parse(new Source($document));
        $expected = ['data' => ['nest' => ['nonNullListOfNonNull' => [1, 2]]]];

        $this->assertEquals($expected, Executor::execute($this->getSchema(), $this->getData(), $ast, 'Q', []));
    }

    public function testHandlesListsWhenTheyReturnNullAsAValue()
    {
        $document = '
            query Q {
                nest {
                    listContainsNull,
                }
            }
        ';

        $parser = new Parser();
        $ast = $parser->parse(new Source($document));
        $expected = ['data' => ['nest' => ['listContainsNull' => [1, NULL, 2]]]];

        $this->assertEquals($expected, Executor::execute($this->getSchema(), $this->getData(), $ast, 'Q', []));
    }

    public function testHandlesListsOfNonNullsWhenTheyReturnNullAsAValue()
    {
        $document = '
            query Q {
                nest {
                    listOfNonNullContainsNull,
                }
            }
        ';

        $parser = new Parser();
        $ast = $parser->parse(new Source($document));

        $expected = [
            'data' => ['nest' => ['listOfNonNullContainsNull' => NULL]],
            'errors' => [new \Exception('Cannot return null for non-nullable type.')],
        ];

        $this->assertEquals($expected, Executor::execute($this->getSchema(), $this->getData(), $ast, 'Q', []));
    }

    public function testHandlesNonNullListsOfWhenTheyReturnNullAsAValue()
    {
        $document = '
            query Q {
                nest {
                    nonNullListContainsNull,
                }
            }
        ';

        $parser = new Parser();
        $ast = $parser->parse(new Source($document));
        $expected = ['data' => ['nest' => ['nonNullListContainsNull' => [1, NULL, 2]]]];

        $this->assertEquals($expected, Executor::execute($this->getSchema(), $this->getData(), $ast, 'Q', []));
    }

    public function testHandlesNonNullListsOfNonNullsWhenTheyReturnNullAsAValue()
    {
        $document = '
            query Q {
                nest {
                    nonNullListOfNonNullContainsNull,
                }
            }
        ';

        $parser = new Parser();
        $ast = $parser->parse(new Source($document));

        $expected = [
            'data' => ['nest' => NULL],
            'errors' => [new \Exception('Cannot return null for non-nullable type.')],
        ];

        $this->assertEquals($expected, Executor::execute($this->getSchema(), $this->getData(), $ast, 'Q', []));
    }

    public function testHandlesListsWhenTheyReturnNull()
    {
        $document = '
            query Q {
                nest {
                    listReturnsNull,
                }
            }
        ';

        $parser = new Parser();
        $ast = $parser->parse(new Source($document));
        $expected = ['data' => ['nest' => ['listReturnsNull' => NULL]]];

        $this->assertEquals($expected, Executor::execute($this->getSchema(), $this->getData(), $ast, 'Q', []));
    }

    public function testHandlesListsOfNonNullsWhenTheyReturnNull()
    {
        $document = '
            query Q {
                nest {
                    listOfNonNullReturnsNull,
                }
            }
        ';

        $parser = new Parser();
        $ast = $parser->parse(new Source($document));
        $expected = ['data' => ['nest' => ['listOfNonNullReturnsNull' => NULL]]];

        $this->assertEquals($expected, Executor::execute($this->getSchema(), $this->getData(), $ast, 'Q', []));
    }

    public function testHandlesNonNullListsOfWhenTheyReturnNull()
    {
        $document = '
            query Q {
                nest {
                    nonNullListReturnsNull,
                }
            }
        ';

        $parser = new Parser();
        $ast = $parser->parse(new Source($document));

        $expected = [
            'data' => ['nest' => NULL],
            'errors' => [new \Exception('Cannot return null for non-nullable type.')],
        ];

        $this->assertEquals($expected, Executor::execute($this->getSchema(), $this->getData(), $ast, 'Q', []));
    }

    public function testHandlesNonNullListsOfNonNullsWhenTheyReturnNull()
    {
        $document = '
            query Q {
                nest {
                    nonNullListOfNonNullReturnsNull,
                }
            }
        ';

        $parser = new Parser();
        $ast = $parser->parse(new Source($document));

        $expected = [
            'data' => ['nest' => NULL],
            'errors' => [new \Exception('Cannot return null for non-nullable type.')],
        ];

        $this->assertEquals($expected, Executor::execute($this->getSchema(), $this->getData(), $ast, 'Q', []));
    }

    protected function getSchema()
    {
        $dataType = new ObjectType('DataType', [
            'list' => [
                'type' => new ListModifier(Type::intType()),
            ],
            'listOfNonNull' => [
                'type' => new ListModifier(new NonNullModifier(Type::intType())),
            ],
            'nonNullList' => [
                'type' => new NonNullModifier(new ListModifier(Type::intType())),
            ],
            'nonNullListOfNonNull' => [
                'type' => new NonNullModifier(new ListModifier(new NonNullModifier(Type::intType()))),
            ],
            'listContainsNull' => [
                'type' => new ListModifier(Type::intType()),
            ],
            'listOfNonNullContainsNull' => [
                'type' => new ListModifier(new NonNullModifier(Type::intType())),
            ],
            'nonNullListContainsNull' => [
                'type' => new NonNullModifier(new ListModifier(Type::intType())),
            ],
            'nonNullListOfNonNullContainsNull' => [
                'type' => new NonNullModifier(new ListModifier(new NonNullModifier(Type::intType()))),
            ],
            'listReturnsNull' => [
                'type' => new ListModifier(Type::intType()),
            ],
            'listOfNonNullReturnsNull' => [
                'type' => new ListModifier(new NonNullModifier(Type::intType())),
            ],
            'nonNullListReturnsNull' => [
                'type' => new NonNullModifier(new ListModifier(Type::intType())),
            ],
            'nonNullListOfNonNullReturnsNull' => [
                'type' => new NonNullModifier(new ListModifier(new NonNullModifier(Type::intType()))),
            ],
            'nest' => ['type' => function () use (&$dataType) {
                return $dataType;
            }],
        ]);

        $schema = new Schema($dataType);
        return $schema;
    }

    protected function getData()
    {
        return [
            'list' => function () {
                return [1, 2];
            },
            'listOfNonNull' => function () {
                return [1, 2];
            },
            'nonNullList' => function () {
                return [1, 2];
            },
            'nonNullListOfNonNull' => function () {
                return [1, 2];
            },
            'listContainsNull' => function () {
                return [1, NULL, 2];
            },
            'listOfNonNullContainsNull' => function () {
                return [1, NULL, 2];
            },
            'nonNullListContainsNull' => function () {
                return [1, NULL, 2];
            },
            'nonNullListOfNonNullContainsNull' => function () {
                return [1, NULL, 2];
            },
            'listReturnsNull' => function () {
                return NULL;
            },
            'listOfNonNullReturnsNull' => function () {
                return NULL;
            },
            'nonNullListReturnsNull' => function () {
                return NULL;
            },
            'nonNullListOfNonNullReturnsNull' => function () {
                return NULL;
            },
            'nest' => function () {
                return self::getData();
            }
        ];
    }
}
