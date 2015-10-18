<?php
/*
 * This file is part of the prooph software processing toolbox.
 * (c) 2014-2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 10.07.14 - 21:40
 */

namespace ProophTest\Done\Process\Type;

use Prooph\Done\Process\Type\Exception\InvalidTypeException;
use Prooph\Done\Process\Type\String;
use Prooph\Done\Process\Type\StringCollection;
use ProophTest\Done\Process\TestCase;

/**
 * Class StringCollectionTest
 *
 * @package ProophTest\Done\Process\Type
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class StringCollectionTest extends TestCase
{
    /**
     * @test
     */
    public function it_constructs_a_collection_from_array_containing_string_types()
    {
        $fruits = StringCollection::fromNativeValue(array(
            "Apple",
            String::fromNativeValue("Banana"),
            "Strawberry"
        ));

        $this->assertInstanceOf('Prooph\Done\Process\Type\StringCollection', $fruits);

        $fruitList = array();

        foreach ($fruits->value() as $fruit) {
            $fruitList[] = $fruit->value();
        }

        $this->assertEquals(array("Apple", "Banana", "Strawberry"), $fruitList);
    }

    /**
     * @test
     */
    public function it_constructs_collection_from_string_representation()
    {
        $fruitsString = json_encode(array("Apple", "Banana", "Strawberry"));

        $fruits = StringCollection::fromString($fruitsString);

        $fruitList = array();

        foreach ($fruits->value() as $fruit) {
            $fruitList[] = $fruit->value();
        }

        $this->assertEquals(array("Apple", "Banana", "Strawberry"), $fruitList);
    }

    /**
     * @test
     */
    public function it_constructs_collection_from_json_decoded_value()
    {
        $fruits = StringCollection::fromNativeValue(array(
            "Apple",
            String::fromNativeValue("Banana"),
            "Strawberry"
        ));

        $jsonString = json_encode($fruits);

        $decodedJson = json_decode($jsonString);

        $decodedFruits = StringCollection::fromJsonDecodedData($decodedJson);

        $fruitList = array();

        foreach ($decodedFruits->value() as $fruit) {
            $fruitList[] = $fruit->value();
        }

        $this->assertEquals(array("Apple", "Banana", "Strawberry"), $fruitList);
    }

    /**
     * @test
     */
    public function it_rejects_value_if_it_is_not_an_array()
    {
        $prototype = null;

        try {
            StringCollection::fromNativeValue("not an array");
        } catch (InvalidTypeException $invalidTypeException) {
            $prototype = $invalidTypeException->getPrototypeOfRelatedType();
        }

        $this->assertInstanceOf('Prooph\Done\Process\Type\Prototype', $prototype);

        $this->assertEquals('Prooph\Done\Process\Type\StringCollection', $prototype->of());
    }

    /**
     * @test
     */
    public function it_rejects_value_if_it_is_not_a_collection_containing_only_one_item_type()
    {
        $prototype = null;

        try {
            $collection = StringCollection::fromNativeValue(array("Apple", 123, "Strawberry"));
            $collection->value();
        } catch (InvalidTypeException $invalidTypeException) {
            $prototype = $invalidTypeException->getPrototypeOfRelatedType();
        }

        $this->assertInstanceOf('Prooph\Done\Process\Type\Prototype', $prototype);

        $this->assertEquals('Prooph\Done\Process\Type\String', $prototype->of());
    }

    /**
     * @test
     */
    public function it_has_a_convenient_description()
    {
        $stringCol = StringCollection::fromNativeValue(array("Apple", "Banana"));

        $description = $stringCol->description();

        $this->assertEquals('StringCollection', $description->label());
        $this->assertEquals('collection', $description->nativeType());
        $this->assertFalse($description->hasIdentifier());
    }

    /**
     * @test
     */
    public function it_has_item_property_but_no_other()
    {
        $stringCol = StringCollection::fromNativeValue(array("Apple", "Banana"));

        $this->assertTrue($stringCol->hasProperty('item'));
        $this->assertFalse($stringCol->hasProperty('Apple'));
    }

    /**
     * @test
     */
    public function it_returns_item_property_if_requested()
    {
        $stringCol = StringCollection::fromNativeValue(array("Apple", "Banana"));

        $this->assertEquals('Prooph\Done\Process\Type\String',$stringCol->property('item')->value());
        $this->assertNull($stringCol->property('Apple'));
    }
}
 