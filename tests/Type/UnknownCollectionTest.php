<?php
/*
 * This file is part of the prooph software processing toolbox.
 * (c) 2014-2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 09.12.14 - 19:15
 */

namespace ProophTest\Processing\Type;

use Prooph\Processing\Type\UnknownCollection;
use ProophTest\Processing\TestCase;

/**
 * Class UnknownCollectionTest
 *
 * @package ProophTest\Processing\Type
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class UnknownCollectionTest extends TestCase
{
    /**
     * @test
     * @dataProvider provideValidValues
     */
    public function it_can_be_initialized_with_valid_values_and_can_be_converted_to_string_or_json_and_back($value)
    {
        $unknownCollection = UnknownCollection::fromNativeValue($value);

        $unknownCollectionString = $unknownCollection->toString();

        $unknownCollectionFromString = UnknownCollection::fromString($unknownCollectionString);

        $this->assertEquals($unknownCollection->value(), $unknownCollectionFromString->value());

        $unknownCollectionJson = json_encode($unknownCollection);

        $unknownCollectionFromJson = UnknownCollection::fromJsonDecodedData(json_decode($unknownCollectionJson, true));

        $this->assertEquals($unknownCollection->value(), $unknownCollectionFromJson->value());
    }

    /**
     * @return array
     */
    public function provideValidValues()
    {
        return [
            [["TestString", "TestString2", "TestString3"]],
            [[10, 11, 12]],
            [[99.99, 100, 100.01]],
            [[false, true]],
            [[true, ["array"], ["mixed" => [1,2,3]]]],
            [[[10, 11, 12], [13, 14, 15]]],
        ];
    }
}
 