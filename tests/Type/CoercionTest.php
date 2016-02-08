<?php

namespace Fubhy\GraphQL\Tests\Type;

use Fubhy\GraphQL\Type\Definition\Types\Type;

class CoercionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider coercesOutputIntProvider
     *
     * @param mixed $input
     * @param int|null $expected
     */
    public function testCoercesOutputInt($input, $expected)
    {
        $this->assertSame($expected, Type::intType()->coerce($input));
    }

    public function coercesOutputIntProvider()
    {
        return [
            [1, 1],
            [0, 0],
            [-1, -1],
            [0.1, 0],
            [1.1, 1],
            [-1.1, -1],
            [1e5, 100000],
            [9876504321, 9876504321],
            [-9876504321, -9876504321],
            [1e100, NULL],
            [-1e100, NULL],
            ['-1.1', -1],
            ['one', NULL],
            [FALSE, 0],
            [TRUE, 1],
        ];
    }

    /**
     * @dataProvider coercesOutputFloatProvider
     *
     * @param mixed $input
     * @param float|null $expected
     */
    public function testCoercesOutputFloat($input, $expected)
    {
        $this->assertSame($expected, Type::floatType()->coerce($input));
    }

    public function coercesOutputFloatProvider()
    {
        return [
          [1, 1.0],
          [0, 0.0],
          [-1, -1.0],
          [0.1, 0.1],
          [1.1, 1.1],
          [-1.1, -1.1],
          ['-1.1', -1.1],
          [FALSE, 0.0],
          [TRUE, 1.0],
        ];
    }

    /**
     * @dataProvider coercesOutputStringProvider
     *
     * @param mixed $input
     * @param float|null $expected
     */
    public function testCoercesOutputString($input, $expected)
    {
        $this->assertSame($expected, Type::stringType()->coerce($input));
    }

    public function coercesOutputStringProvider() {
        return [
            ['string', 'string'],
            [1, '1'],
            [-1.1, '-1.1'],
            [TRUE, 'true'],
            [FALSE, 'false'],
        ];
    }

    /**
     * @dataProvider coercesOutputBooleanProvider
     *
     * @param mixed $input
     * @param float|null $expected
     */
    public function testCoercesOutputBoolean($input, $expected)
    {
       $this->assertSame($expected, Type::booleanType()->coerce($input));
    }

    public function coercesOutputBooleanProvider()
    {
        return [
            ['string', TRUE],
            ['', FALSE],
            [1, TRUE],
            [0, FALSE],
            [TRUE, TRUE],
            [FALSE, FALSE],
        ];
    }
}
