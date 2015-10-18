<?php
/*
 * This file is part of the prooph software processing toolbox.
 * (c) 2014-2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.07.14 - 21:12
 */

namespace Prooph\Done\Process\Type;

use Prooph\Done\Process\Type\Description\Description;
use Prooph\Done\Process\Type\Exception\InvalidTypeException;

/**
 * Abstract Class SingleValue
 *
 * Prooph\Done\Process\Type that transports just one value
 *
 * @package Prooph\Done\Process\Type
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
abstract class SingleValue implements Type
{
    /**
     * @var mixed
     */
    protected $value;

    /**
     * @var Description
     */
    protected $description;

    /**
     * @return Prototype
     */
    public static function prototype()
    {
        $implementer = get_called_class();

        if (PrototypeRegistry::hasPrototype($implementer)) return PrototypeRegistry::getPrototype($implementer);

        return new Prototype(get_called_class(), static::buildDescription(), array());
    }

    /**
     * Performs assertions and sets the internal value property on success
     *
     * @param mixed $value
     * @return void
     */
    abstract protected function setValue($value);

    /**
     * @param mixed $value
     * @return Type
     */
    public static function fromNativeValue($value)
    {
        return new static($value);
    }

    /**
     * @param mixed $value
     * @return Type
     */
    public static function fromJsonDecodedData($value)
    {
        return static::fromNativeValue($value);
    }

    /**
     * Non accessible construct
     *
     * Use static factory methods to construct a SingleValue
     *
     * @param mixed $value
     * @throws Exception\InvalidTypeException
     */
    protected function __construct($value)
    {
        try {
            $this->setValue($value);
        } catch (\InvalidArgumentException $ex) {
            throw InvalidTypeException::fromInvalidArgumentExceptionAndPrototype($ex, static::prototype());
        }

    }

    /**
     * @return Description
     */
    public function description()
    {
        if (is_null($this->description)) {
            $this->description = static::buildDescription();
        }

        return $this->description;
    }

    /**
     * A single value has no properties so method always returns an empty list
     *
     * @return Property[]
     */
    public function properties()
    {
        return array();
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasProperty($name)
    {
        return false;
    }

    /**
     * @param string $name
     * @return Property|null
     */
    public function property($name)
    {
        return null;
    }

    /**
     * @return mixed Type of the value is defined in Prooph\Done\Process\Type\Description of the type
     */
    public function value()
    {
        return $this->value;
    }

    /**
     * @return string representation of the value
     */
    public function toString()
    {
        return (string)$this->value;
    }

    /**
     * @return mixed
     */
    public function jsonSerialize()
    {
        return $this->value();
    }

    /**
     * @param Type $other
     * @return bool
     */
    public function sameAs(Type $other)
    {
        if (! $other instanceof SingleValue) {
            return false;
        }

        return $this->value() === $other->value();
    }
}
 