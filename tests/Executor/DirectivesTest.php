<?php

namespace Fubhy\GraphQL\Tests\Executor;

use Fubhy\GraphQL\Executor\Executor;
use Fubhy\GraphQL\Language\Parser;
use Fubhy\GraphQL\Language\Source;
use Fubhy\GraphQL\Schema;
use Fubhy\GraphQL\Type\Definition\Types\ObjectType;
use Fubhy\GraphQL\Type\Definition\Types\Type;

class DirectivesTest extends \PHPUnit_Framework_TestCase
{
    public function testWorksWithoutDirectives()
    {
        $this->assertEquals(['data' => ['a' => 'a', 'b' => 'b']], $this->executeTestQuery('{ a, b }'));
    }

    public function testWorksOnScalars()
    {
        $this->assertEquals(['data' => ['a' => 'a', 'b' => 'b']], $this->executeTestQuery('{ a, b @include(if: true) }'));
        $this->assertEquals(['data' => ['a' => 'a']], $this->executeTestQuery('{ a, b @include(if: false) }'));
        $this->assertEquals(['data' => ['a' => 'a', 'b' => 'b']], $this->executeTestQuery('{ a, b @skip(if: false) }'));
        $this->assertEquals(['data' => ['a' => 'a']], $this->executeTestQuery('{ a, b @skip(if: true) }'));
    }

    public function testWorksOnFragmentSpreads()
    {
        $query = '
            query Q {
                a
                ...Frag @include(if: false)
            }

            fragment Frag on TestType {
                b
            }
        ';

        $this->assertEquals(['data' => ['a' => 'a']], $this->executeTestQuery($query));

        $query = '
            query Q {
                a
                ...Frag @include(if: true)
            }

            fragment Frag on TestType {
                b
            }
        ';

        $this->assertEquals(['data' => ['a' => 'a', 'b' => 'b']], $this->executeTestQuery($query));

        $query = '
            query Q {
                a
                ...Frag @skip(if: false)
            }

            fragment Frag on TestType {
                b
            }
        ';

        $this->assertEquals(['data' => ['a' => 'a', 'b' => 'b']], $this->executeTestQuery($query));

        $query = '
            query Q {
                a
                ...Frag @skip(if: true)
            }

            fragment Frag on TestType {
                b
            }
        ';

        $this->assertEquals(['data' => ['a' => 'a']], $this->executeTestQuery($query));
    }

    public function testWorksOnInlineFragment()
    {
        $query = '
            query Q {
                a
                ... on TestType @include(if: false) {
                    b
                }
            }

            fragment Frag on TestType {
                b
            }
        ';

        $this->assertEquals(['data' => ['a' => 'a']], $this->executeTestQuery($query));

        $query = '
            query Q {
                a
                ... on TestType @include(if: true) {
                    b
                }
            }

            fragment Frag on TestType {
                b
            }
        ';

        $this->assertEquals(['data' => ['a' => 'a', 'b' => 'b']], $this->executeTestQuery($query));

        $query = '
            query Q {
                a
                ... on TestType @skip(if: false) {
                    b
                }
            }

            fragment Frag on TestType {
                b
            }
        ';

        $this->assertEquals(['data' => ['a' => 'a', 'b' => 'b']], $this->executeTestQuery($query));

        $query = '
            query Q {
                a
                ... on TestType @skip(if: true) {
                    b
                }
            }

            fragment Frag on TestType {
                b
            }
        ';

        $this->assertEquals(['data' => ['a' => 'a']], $this->executeTestQuery($query));
    }

    public function testWorksOnFragment()
    {
        $query = '
            query Q {
                a
                ...Frag
            }

            fragment Frag on TestType @include(if: false) {
                b
            }
        ';

        $this->assertEquals(['data' => ['a' => 'a']], $this->executeTestQuery($query));

        $query = '
            query Q {
                a
                ...Frag
            }

            fragment Frag on TestType @include(if: true) {
                b
            }
        ';
        $this->assertEquals(['data' => ['a' => 'a', 'b' => 'b']], $this->executeTestQuery($query));

        $query = '
            query Q {
                a
                ...Frag
            }

            fragment Frag on TestType @skip(if: false) {
                b
            }
        ';

        $this->assertEquals(['data' => ['a' => 'a', 'b' => 'b']], $this->executeTestQuery($query));

        $query = '
            query Q {
                a
                ...Frag
            }

            fragment Frag on TestType @skip(if: true) {
                b
            }
        ';

        $this->assertEquals(['data' => ['a' => 'a']], $this->executeTestQuery($query));
    }

    protected function executeTestQuery($document)
    {
        $data = [
            'a' => function () {
                return 'a';
            },
            'b' => function () {
                return 'b';
            }
        ];

        $schema = new Schema(new ObjectType('TestType', [
            'a' => ['type' => Type::stringType()],
            'b' => ['type' => Type::stringType()],
        ]));

        $parser = new Parser();
        return Executor::execute($schema, $data, $parser->parse(new Source($document)));
    }
}
