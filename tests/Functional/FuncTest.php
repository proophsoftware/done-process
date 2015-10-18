<?php
/**
 * This file is part of the prooph software processing toolbox.
 * (c) 2014-2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ProophTest\Done\Process\Functional;

use Prooph\Done\Process\Functional\Func;
use Prooph\Done\Process\Functional\Iterator\MapIterator;
use Prooph\Done\Process\Type\String;
use Prooph\Done\Process\Type\StringCollection;
use ProophTest\Done\Process\TestCase;

/**
 * Class FuncTest
 *
 * @package ProophTest\Done\Process\Functional
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class FuncTest extends TestCase
{
    /**
     * @test
     */
    function elements_of_collection_are_mapped_to_callback()
    {
        $numbers = [1,2,3];

        $squareNumbers = Func::map($numbers, function($number) { return $number * $number; });

        $this->assertEquals([1,4,9], $squareNumbers);
    }

    /**
     * @test
     */
    function it_returns_a_callable_method()
    {
        $map = Func::get('map');

        $numbers = [1,2,3];

        $squareNumbers = $map($numbers, function($number) { return $number * $number; });

        $this->assertEquals([1,4,9], $squareNumbers);
    }

    /**
     * @test
     */
    function it_prepares_a_function_skipping_the_first_argument()
    {
        $calculateSquareNumbers = Func::prepare('map', null, function($number) { return $number * $number; });

        $numbers = [1,2,3];

        $squareNumbers = $calculateSquareNumbers($numbers);

        $this->assertEquals([1,4,9], $squareNumbers);
    }

    /**
     * @test
     */
    function it_prepares_a_function_skipping_the_last_argument()
    {
        $mappableNumbers = Func::prepare('map', [1,2,3], null);

        $this->assertEquals([1,4,9], $mappableNumbers(function($number) { return $number * $number; }));
    }

    /**
     * @test
     */
    public function it_applies_premap_callback_to_payload_collection()
    {
        $stringCollection = [
            "a string",
            100,
            "yet another string"
        ];

        $collection = StringCollection::fromNativeValue($stringCollection);

        $string_cast = Func::prepare('premap', null, function($item, $key, \Iterator $collection) {
            return (string)$item;
        });

        $collection = $string_cast($collection);

        $this->assertEquals([
            "a string",
            "100",
            "yet another string"
        ], iterator_to_array(new MapIterator($collection, function (String $string) { return $string->value();})));
    }
} 