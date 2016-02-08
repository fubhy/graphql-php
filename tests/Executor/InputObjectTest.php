<?php

namespace Fubhy\GraphQL\Tests\Executor;

use Fubhy\GraphQL\Executor\Executor;
use Fubhy\GraphQL\Language\Source;
use Fubhy\GraphQL\Type\Definition\Types\InputObjectType;
use Fubhy\GraphQL\Type\Definition\Types\ListModifier;
use Fubhy\GraphQL\Type\Definition\Types\NonNullModifier;
use Fubhy\GraphQL\Type\Definition\Types\ObjectType;
use Fubhy\GraphQL\Language\Parser;
use Fubhy\GraphQL\Schema;
use Fubhy\GraphQL\Type\Definition\Types\Type;

class InputObjectTest extends \PHPUnit_Framework_TestCase
{
    public function testUsingInlineStructs()
    {
        $document = '
            {
                fieldWithObjectInput(input: {a: "foo", b: ["bar"], c: "baz"})
            }
        ';

        $parser = new Parser();
        $ast = $parser->parse(new Source($document));
        $expected = ['data' => ['fieldWithObjectInput' => '{"a":"foo","b":["bar"],"c":"baz"}']];
        $this->assertEquals($expected, Executor::execute($this->getSchema(), NULL, $ast));

        $document = '
            {
                fieldWithObjectInput(input: {a: "foo", b: "bar", c: "baz"})
            }
        ';

        $parser = new Parser();
        $ast = $parser->parse(new Source($document));
        $expected = ['data' => ['fieldWithObjectInput' => '{"a":"foo","b":["bar"],"c":"baz"}']];
        $this->assertEquals($expected, Executor::execute($this->getSchema(), NULL, $ast));
    }

    public function testUsingVariables()
    {
        $document = '
            query q($input:TestInputObject) {
                fieldWithObjectInput(input: $input)
            }
        ';

        $parser = new Parser();
        $ast = $parser->parse(new Source($document));
        $schema = $this->getSchema();

        $params = ['input' => ['a' => 'foo', 'b' => ['bar'], 'c' => 'baz']];
        $expected = ['data' => ['fieldWithObjectInput' => '{"a":"foo","b":["bar"],"c":"baz"}']];
        $this->assertEquals($expected, Executor::execute($schema, NULL, $ast, NULL, $params));

        $params = ['input' => ['a' => 'foo', 'b' => 'bar', 'c' => 'baz']];
        $expected = ['data' => ['fieldWithObjectInput' => '{"a":"foo","b":["bar"],"c":"baz"}']];
        $this->assertEquals($expected, Executor::execute($schema, NULL, $ast, NULL, $params));

        $params = ['input' => ['a' => 'foo', 'b' => 'bar', 'c' => NULL]];
        $expected = [
            'data' => NULL,
            'errors' => [new \Exception('Variable $input expected value of different type.')],
        ];

        $this->assertEquals($expected, Executor::execute($schema, NULL, $ast, NULL, $params));

        $params = ['input' => ['a' => 'foo', 'b' => 'bar']];
        $expected = [
            'data' => NULL,
            'errors' => [new \Exception('Variable $input expected value of different type.')],
        ];

        $this->assertEquals($expected, Executor::execute($schema, NULL, $ast, NULL, $params));
    }

    public function testAllowsNullableInputsToBeOmitted()
    {
        $document = '
            {
                fieldWithNullableStringInput
            }
        ';

        $parser = new Parser();
        $ast = $parser->parse(new Source($document));
        $expected = ['data' => ['fieldWithNullableStringInput' => 'null']];

        $this->assertEquals($expected, Executor::execute($this->getSchema(), NULL, $ast));
    }

    public function testAllowsNullableInputsToBeOmittedInAVariable()
    {
        $document = '
            query SetsNullable($value: String) {
                fieldWithNullableStringInput(input: $value)
            }
        ';

        $parser = new Parser();
        $ast = $parser->parse(new Source($document));
        $expected = ['data' => ['fieldWithNullableStringInput' => 'null']];

        $this->assertEquals($expected, Executor::execute($this->getSchema(), NULL, $ast));
    }

    public function testAllowsNullableInputsToBeOmittedInAnUnlistedVariable()
    {
        $document = '
            query SetsNullable {
                fieldWithNullableStringInput(input: $value)
            }
        ';

        $parser = new Parser();
        $ast = $parser->parse(new Source($document));
        $expected = ['data' => ['fieldWithNullableStringInput' => 'null']];

        $this->assertEquals($expected, Executor::execute($this->getSchema(), NULL, $ast));
    }

    public function testAllowsNullableInputsToBeSetToNullInAVariable()
    {
        $document = '
            query SetsNullable($value: String) {
                fieldWithNullableStringInput(input: $value)
            }
        ';

        $parser = new Parser();
        $ast = $parser->parse(new Source($document));
        $expected = ['data' => ['fieldWithNullableStringInput' => 'null']];

        $this->assertEquals($expected, Executor::execute($this->getSchema(), NULL, $ast, NULL, ['value' => NULL]));
    }

    public function testAllowsNullableInputsToBeSetToNullDirectly()
    {
        $document = '
            {
                fieldWithNullableStringInput(input: null)
            }
        ';

        $parser = new Parser();
        $ast = $parser->parse(new Source($document));
        $expected = ['data' => ['fieldWithNullableStringInput' => 'null']];

        $this->assertEquals($expected, Executor::execute($this->getSchema(), NULL, $ast));
    }

    public function testAllowsNullableInputsToBeSetToAValueInAVariable()
    {
        $document = '
            query SetsNullable($value: String) {
                fieldWithNullableStringInput(input: $value)
            }
        ';

        $parser = new Parser();
        $ast = $parser->parse(new Source($document));
        $expected = ['data' => ['fieldWithNullableStringInput' => '"a"']];

        $this->assertEquals($expected, Executor::execute($this->getSchema(), NULL, $ast, NULL, ['value' => 'a']));
    }

    public function testAllowsNullableInputsToBeSetToAValueDirectly()
    {
        $document = '
            {
                fieldWithNullableStringInput(input: "a")
            }
        ';

        $parser = new Parser();
        $ast = $parser->parse(new Source($document));
        $expected = ['data' => ['fieldWithNullableStringInput' => '"a"']];

        $this->assertEquals($expected, Executor::execute($this->getSchema(), NULL, $ast));
    }

    public function testDoesntAllowNonNullableInputsToBeOmittedInAVariable()
    {
        $document = '
            query SetsNonNullable($value: String!) {
                fieldWithNonNullableStringInput(input: $value)
            }
        ';

        $parser = new Parser();
        $ast = $parser->parse(new Source($document));
        $expected = [
            'data' => NULL,
            'errors' => [new \Exception('Variable $value expected value of different type.')],
        ];

        $this->assertEquals($expected, Executor::execute($this->getSchema(), NULL, $ast));
    }

    public function testDoesNotAllowNonNullableInputsToBeSetToNullInAVariable()
    {
        $document = '
            query SetsNonNullable($value: String!) {
                fieldWithNonNullableStringInput(input: $value)
            }
        ';

        $parser = new Parser();
        $ast = $parser->parse(new Source($document));
        $expected = [
            'data' => NULL,
            'errors' => [new \Exception('Variable $value expected value of different type.')],
        ];

        $this->assertEquals($expected, Executor::execute($this->getSchema(), NULL, $ast, NULL, ['value' => NULL]));
    }

    public function testAllowsNonNullableInputsToBeSetToAValueInAVariable()
    {
        $document = '
            query SetsNonNullable($value: String!) {
                fieldWithNonNullableStringInput(input: $value)
            }
        ';

        $parser = new Parser();
        $ast = $parser->parse(new Source($document));
        $expected = ['data' => ['fieldWithNonNullableStringInput' => '"a"']];

        $this->assertEquals($expected, Executor::execute($this->getSchema(), NULL, $ast, NULL, ['value' => 'a']));
    }

    public function testAllowsNonNullableInputsToBeSetToAValueDirectly()
    {
        $document = '
            {
                fieldWithNonNullableStringInput(input: "a")
            }
        ';

        $parser = new Parser();
        $ast = $parser->parse(new Source($document));
        $expected = ['data' => ['fieldWithNonNullableStringInput' => '"a"']];

        $this->assertEquals($expected, Executor::execute($this->getSchema(), NULL, $ast));
    }

    public function testPassesAlongNullForNonNullableInputsIfExplcitlySetInTheQuery()
    {
        $document = '
            {
                fieldWithNonNullableStringInput
            }
        ';

        $parser = new Parser();
        $ast = $parser->parse(new Source($document));
        $expected = ['data' => ['fieldWithNonNullableStringInput' => 'null']];

        $this->assertEquals($expected, Executor::execute($this->getSchema(), NULL, $ast));
    }

    public function testAllowsListsToBeNull()
    {
        $document = '
            query q($input:[String]) {
                list(input: $input)
            }
        ';

        $parser = new Parser();
        $ast = $parser->parse(new Source($document));
        $expected = ['data' => ['list' => 'null']];

        $this->assertEquals($expected, Executor::execute($this->getSchema(), NULL, $ast, NULL, ['input' => NULL]));
    }

    public function testAllowsListsToContainValues()
    {
        $document = '
            query q($input:[String]) {
                list(input: $input)
            }
        ';

        $parser = new Parser();
        $ast = $parser->parse(new Source($document));
        $expected = ['data' => ['list' => '["A"]']];

        $this->assertEquals($expected, Executor::execute($this->getSchema(), NULL, $ast, NULL, ['input' => ['A']]));
    }

    public function testAllowsListsToContainNull()
    {
        $document = '
            query q($input:[String]) {
                list(input: $input)
            }
        ';

        $parser = new Parser();
        $ast = $parser->parse(new Source($document));
        $expected = ['data' => ['list' => '["A",null,"B"]']];

        $this->assertEquals($expected, Executor::execute($this->getSchema(), NULL, $ast, NULL, ['input' => ['A',NULL,'B']]));
    }

    public function testDoesNotAllowNonNullListsToBeNull()
    {
        $document = '
            query q($input:[String]!) {
                nnList(input: $input)
            }
        ';

        $parser = new Parser();
        $ast = $parser->parse(new Source($document));
        $expected = [
            'data' => NULL,
            'errors' => [new \Exception('Variable $input expected value of different type.')],
        ];

        $this->assertEquals($expected, Executor::execute($this->getSchema(), NULL, $ast, NULL, ['input' => NULL]));
    }

    public function testAllowsNonNullListsToContainValues()
    {
        $document = '
            query q($input:[String]!) {
                nnList(input: $input)
            }
        ';

        $parser = new Parser();
        $ast = $parser->parse(new Source($document));
        $expected = ['data' => ['nnList' => '["A"]']];

        $this->assertEquals($expected, Executor::execute($this->getSchema(), NULL, $ast, NULL, ['input' => 'A']));
    }

    public function testAllowsNonNullListsToContainNull()
    {
        $document = '
            query q($input:[String]!) {
                nnList(input: $input)
            }
        ';

        $parser = new Parser();
        $ast = $parser->parse(new Source($document));
        $expected = ['data' => ['nnList' => '["A",null,"B"]']];

        $this->assertEquals($expected, Executor::execute($this->getSchema(), NULL, $ast, NULL, ['input' => ['A',NULL,'B']]));
    }

    public function testAllowsListsOfNonNullsToBeNull()
    {
        $document = '
            query q($input:[String!]) {
                listNN(input: $input)
            }
        ';

        $parser = new Parser();
        $ast = $parser->parse(new Source($document));
        $expected = ['data' => ['listNN' => 'null']];

        $this->assertEquals($expected, Executor::execute($this->getSchema(), NULL, $ast, NULL, ['input' => NULL]));
    }

    public function testAllowsListsOfNonNullsToContainValues()
    {
        $document = '
            query q($input:[String!]) {
                listNN(input: $input)
            }
        ';

        $parser = new Parser();
        $ast = $parser->parse(new Source($document));
        $expected = ['data' => ['listNN' => '["A"]']];

        $this->assertEquals($expected, Executor::execute($this->getSchema(), NULL, $ast, NULL, ['input' => 'A']));
    }

    public function testDoesNotAllowListsOfNonNullsToContainNull()
    {
        $document = '
            query q($input:[String!]) {
                listNN(input: $input)
            }
        ';

        $parser = new Parser();
        $ast = $parser->parse(new Source($document));
        $expected = [
            'data' => NULL,
            'errors' => [new \Exception('Variable $input expected value of different type.')],
        ];

        $this->assertEquals($expected, Executor::execute($this->getSchema(), NULL, $ast, NULL, ['input' => ['A', NULL, 'B']]));
    }

    public function testDoesNotAllowNonNullListsOfNonNullsToBeNull()
    {
        $document = '
            query q($input:[String!]!) {
                nnListNN(input: $input)
            }
        ';

        $parser = new Parser();
        $ast = $parser->parse(new Source($document));
        $expected = [
            'data' => NULL,
            'errors' => [new \Exception('Variable $input expected value of different type.')],
        ];

        $this->assertEquals($expected, Executor::execute($this->getSchema(), NULL, $ast, NULL, ['input' => NULL]));
    }

    public function testAllowsNonNullListsOfNonNullsToContainValues()
    {
        $document = '
            query q($input:[String!]!) {
                nnListNN(input: $input)
            }
        ';

        $parser = new Parser();
        $ast = $parser->parse(new Source($document));
        $expected = ['data' => ['nnListNN' => '["A"]']];

        $this->assertEquals($expected, Executor::execute($this->getSchema(), NULL, $ast, NULL, ['input' => ['A']]));
    }

    public function testDoesNotAllowNonNullListsOfNonNullsToContainNull()
    {
        $document = '
            query q($input:[String!]!) {
                nnListNN(input: $input)
            }
        ';

        $parser = new Parser();
        $ast = $parser->parse(new Source($document));
        $expected = [
            'data' => NULL,
            'errors' => [new \Exception('Variable $input expected value of different type.')],
        ];

        $this->assertEquals($expected, Executor::execute($this->getSchema(), NULL, $ast, NULL, ['input' => ['A',NULL,'B']]));
    }

    protected function getSchema()
    {
        $testInputObject = new InputObjectType('TestInputObject', [
            'a' => ['type' => Type::stringType()],
            'b' => ['type' => new ListModifier(Type::stringType())],
            'c' => ['type' => new NonNullModifier(Type::stringType())]
        ]);

        $testType = new ObjectType('TestType', [
            'fieldWithObjectInput' => [
                'type' => Type::stringType(),
                'args' => ['input' => ['type' => $testInputObject]],
                'resolve' => function ($_, $args) {
                    return json_encode($args['input']);
                }
            ],
            'fieldWithNullableStringInput' => [
                'type' => Type::stringType(),
                'args' => ['input' => ['type' => Type::stringType()]],
                'resolve' => function ($_, $args) {
                    return json_encode($args['input']);
                }
            ],
            'fieldWithNonNullableStringInput' => [
                'type' => Type::stringType(),
                'args' => ['input' => ['type' => new NonNullModifier(Type::stringType())]],
                'resolve' => function ($_, $args) {
                    return json_encode($args['input']);
                }
            ],
            'list' => [
                'type' => Type::stringType(),
                'args' => ['input' => ['type' => new ListModifier(Type::stringType())]],
                'resolve' => function ($_, $args) {
                    return json_encode($args['input']);
                }
            ],
            'nnList' => [
                'type' => Type::stringType(),
                'args' => ['input' => ['type' => new NonNullModifier(new ListModifier(Type::stringType()))]],
                'resolve' => function ($_, $args) {
                    return json_encode($args['input']);
                }
            ],
            'listNN' => [
                'type' => Type::stringType(),
                'args' => ['input' => ['type' => new ListModifier(new NonNullModifier(Type::stringType()))]],
                'resolve' => function ($_, $args) {
                    return json_encode($args['input']);
                }
            ],
            'nnListNN' => [
                'type' => Type::stringType(),
                'args' => ['input' => ['type' => new NonNullModifier(new ListModifier(new NonNullModifier(Type::stringType())))]],
                'resolve' => function ($_, $args) {
                    return json_encode($args['input']);
                }
            ],
        ]);

        $schema = new Schema($testType);
        return $schema;
    }
}
