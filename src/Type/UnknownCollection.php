<?php
/*
 * This file is part of the prooph software processing toolbox.
 * (c) 2014-2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 09.12.14 - 19:13
 */

namespace Prooph\Done\Process\Type;

use Prooph\Done\Process\Type\Description\Description;
use Prooph\Done\Process\Type\Description\DescriptionRegistry;
use Prooph\Done\Process\Type\Description\NativeType;

/**
 * Class UnknownCollection
 *
 * @package Prooph\Done\Process\Type
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class UnknownCollection extends AbstractCollection
{
    /**
     * Returns the prototype of the items type
     *
     * A collection has always one property with name item representing the type of all items in the collection.
     *
     * @return Prototype
     */
    public static function itemPrototype()
    {
        return Unknown::prototype();
    }

    /**
     * @return Description
     */
    public static function buildDescription()
    {
        if (DescriptionRegistry::hasDescription(__CLASS__)) return DescriptionRegistry::getDescription(__CLASS__);

        $desc = new Description('UnknownCollection', NativeType::COLLECTION, false);

        DescriptionRegistry::registerDescriptionFor(__CLASS__, $desc);

        return $desc;
    }
}
 