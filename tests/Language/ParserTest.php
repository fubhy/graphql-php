<?php

namespace Fubhy\GraphQL\Tests\Tests\Language;

use Fubhy\GraphQL\Language\Location;
use Fubhy\GraphQL\Language\Node\Argument;
use Fubhy\GraphQL\Language\Node\Document;
use Fubhy\GraphQL\Language\Node\Field;
use Fubhy\GraphQL\Language\Node\IntValue;
use Fubhy\GraphQL\Language\Node\Name;
use Fubhy\GraphQL\Language\Node\OperationDefinition;
use Fubhy\GraphQL\Language\Node\SelectionSet;
use Fubhy\GraphQL\Language\Parser;
use Fubhy\GraphQL\Language\Source;

class ParserTest extends \PHPUnit_Framework_TestCase
{
    public function testParseProvidesUsefulErrors()
    {
        // @todo Implement this after porting exceptions.
    }

    public function testParseProvidesUsefulErrorWhenUsingSource()
    {
        // @todo Implement this after porting exceptions.
    }

    public function testParsesVariableInlineValues()
    {
        $source = '{ field(complex: { a: { b: [ $var ] } }) }';
        $parser = new Parser();
        $parser->parse(new Source($source));
    }

    public function testParsesConstantDefaultValues()
    {
        // @todo Implement this after porting exceptions.
    }

    public function testDuplicateKeysInInputObjectIsSyntaxError()
    {
        // @todo Implement this after porting exceptions.
    }

    public function testParsesKitchenSink()
    {
        $source = file_get_contents(__DIR__ . '/kitchen-sink.graphql');
        $parser = new Parser();
        $parser->parse(new Source($source));
    }

    public function testParseCreatesAst()
    {
        $source = new Source('
            {
                node(id: 4) {
                    id,
                    name
                }
            }
        ');

        $parser = new Parser();
        $result = $parser->parse($source);

        $expected = new Document([
            new OperationDefinition('query', NULL, [], [],
                new SelectionSet([
                    new Field(
                        new Name('node', new Location(31, 35, $source)), NULL, [
                            new Argument(
                                new Name('id', new Location(36, 38, $source)),
                                new IntValue('4', new Location(40, 41, $source)),
                                new Location(36, 41, $source)
                            )
                        ], [],
                        new SelectionSet(
                            [
                                new Field(
                                    new Name('id', new Location(65, 67, $source)), NULL, [], [], NULL,
                                    new Location(65, 67, $source)
                                ),
                                new Field(
                                    new Name('name', new Location(89, 93, $source)), NULL, [], [], NULL,
                                    new Location(89, 93, $source)
                                ),
                            ], new Location(43, 111, $source)),
                        new Location(31, 111, $source))
                    ], new Location(13, 125, $source)
                ), new Location(13, 125, $source))
            ], new Location(13, 134, $source)
        );

        $this->assertEquals($expected, $result);
    }
}
