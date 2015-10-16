<?php
/*
 * This file is part of the prooph software processing toolbox.
 * (c) 2014-2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 11.07.14 - 20:18
 */

namespace ProophTest\Processing\Mock;

use Prooph\Processing\Type\AbstractDictionary;
use Prooph\Processing\Type\Description\Description;
use Prooph\Processing\Type\Description\NativeType;
use Prooph\Processing\Type\Integer;
use Prooph\Processing\Type\String;

class AddressDictionary extends AbstractDictionary
{
    /**
     * @return array[propertyName => Prototype]
     */
    public static function getPropertyPrototypes()
    {
        return array(
            'street' => String::prototype(),
            'streetNumber' => Integer::prototype(),
            'zip' => String::prototype(),
            'city' => String::prototype()
        );
    }

    /**
     * @return Description
     */
    public static function buildDescription()
    {
        return new Description("Address", NativeType::DICTIONARY, false);
    }
}
 