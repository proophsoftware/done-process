<?php
/*
 * This file is part of the prooph software processing toolbox.
 * (c) 2014-2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 09.07.14 - 20:26
 */

namespace Prooph\Done\Process\Type;

use Assert\Assertion;
use Prooph\Done\Process\Type\Description\Description;
use Prooph\Done\Process\Type\Description\DescriptionRegistry;
use Prooph\Done\Process\Type\Description\NativeType;

/**
 * Class DateTime
 *
 * @package Prooph\Done\Process\Type
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class DateTime extends SingleValue
{
    /**
     * @return \DateTime|null
     */
    public function value()
    {
        return clone $this->value;
    }

    /**
     * Performs assertions and sets the internal value property on success
     *
     * @param mixed $value
     * @return void
     */
    protected function setValue($value)
    {
        Assertion::isInstanceOf($value, '\DateTime');

        $this->value = clone $value;
    }

    /**
     * The description is cached in the internal description property
     *
     * Implement the method to build the description only once and only if it is requested
     *
     * @return Description
     */
    public static function buildDescription()
    {
        if (DescriptionRegistry::hasDescription(__CLASS__)) return DescriptionRegistry::getDescription(__CLASS__);

        $desc = new Description('DateTime', NativeType::DATETIME, false);

        DescriptionRegistry::registerDescriptionFor(__CLASS__, $desc);

        return $desc;
    }

    /**
     * @param string $valueString
     * @return Type
     */
    public static function fromString($valueString)
    {
        return new static(new \DateTime($valueString));
    }

    /**
     * @param mixed $value
     * @return Type
     */
    public static function fromJsonDecodedData($value)
    {
        return static::fromString($value);
    }

    /**
     * @return string
     */
    public function toString()
    {
        if (is_null($this->value)) {
            return "";
        } else {
            return $this->value->format(\DateTime::ISO8601);
        }
    }

    /**
     * @return string
     */
    public function jsonSerialize()
    {
        return $this->toString();
    }

    /**
     * @param Type $other
     * @return bool
     */
    public function sameAs(Type $other)
    {
        if (! $other instanceof DateTime) {
            return false;
        }

        return $this->toString() === $other->toString();
    }
}
 