<?php
/*
 * This file is part of the prooph software processing toolbox.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 28.01.15 - 22:14
 */

namespace ProophTest\Done\Process\Type;

use Prooph\Done\Process\Type\BooleanOrNull;
use Prooph\Done\Process\Type\Exception\InvalidTypeException;
use ProophTest\Done\Process\TestCase;

/**
 * Class BooleanOrNullTest
 *
 * @package ProophTest\Done\Process\Type
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class BooleanOrNullTest extends TestCase
{
    /**
     * @test
     * @dataProvider provideValues
     */
    public function it_constructs_new_instance_correctly($possibleValue, $shouldBeNull)
    {
        $boolOrNull = BooleanOrNull::fromNativeValue($possibleValue);

        $this->assertInstanceOf('Prooph\Done\Process\Type\BooleanOrNull', $boolOrNull);
        $this->assertSame($possibleValue, $boolOrNull->value());

        $asString = $boolOrNull->toString();

        $fromString = BooleanOrNull::fromString($asString);

        $this->assertTrue($boolOrNull->sameAs($fromString));

        $asJson = json_encode($boolOrNull);

        $fromJson = BooleanOrNull::fromJsonDecodedData(json_decode($asJson));

        $this->assertTrue($boolOrNull->sameAs($fromJson));
        $this->assertSame($shouldBeNull, $boolOrNull->isNull());
    }

    public function provideValues()
    {
        return [
            [
                true,
                false,
            ],
            [
                false,
                false,
            ],
            [
                null,
                true,
            ]
        ];
    }

    /**
     * @test
     */
    public function it_rejects_value_if_it_is_not_an_boolean()
    {
        $prototype = null;

        try {
            BooleanOrNull::fromNativeValue(1);
        } catch (InvalidTypeException $invalidTypeException) {
            $prototype = $invalidTypeException->getPrototypeOfRelatedType();
        }

        $this->assertInstanceOf('Prooph\Done\Process\Type\Prototype', $prototype);

        $this->assertEquals('Prooph\Done\Process\Type\BooleanOrNull', $prototype->of());

    }

    /**
     * @test
     */
    public function it_has_a_convenient_description()
    {
        $bool = BooleanOrNull::fromNativeValue(true);

        $description = $bool->description();

        $this->assertEquals('Boolean', $description->label());
        $this->assertEquals('boolean', $description->nativeType());
        $this->assertFalse($description->hasIdentifier());
    }

    /**
     * @test
     */
    public function it_constructs_a_prototype()
    {
        $boolPrototype = BooleanOrNull::prototype();

        $this->assertEquals('Prooph\Done\Process\Type\BooleanOrNull', $boolPrototype->of());

        $description = $boolPrototype->typeDescription();

        $this->assertEquals('Boolean', $description->label());
        $this->assertEquals('boolean', $description->nativeType());
        $this->assertFalse($description->hasIdentifier());
    }
}
 