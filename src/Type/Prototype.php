<?php
/*
 * This file is part of the prooph software processing toolbox.
 * (c) 2014-2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 09.07.14 - 21:34
 */

namespace Prooph\Done\Process\Type;

use Assert\Assertion;
use Prooph\Done\Process\Type\Description\Description;

/**
 * Class Prototype
 *
 * @package Prooph\Done\Process\Type
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class Prototype 
{
    /**
     * @var string
     */
    protected $relatedTypeClass;

    /**
     * @var Description
     */
    protected $descriptionOfType;

    /**
     * @var PrototypeProperty[propertyName => PrototypeProperty]
     */
    protected $typeProperties;

    /**
     * @param string $relatedTypeClass
     * @param Description $descriptionOfType
     * @param PrototypeProperty[] $typeProperties
     */
    public function __construct($relatedTypeClass, Description $descriptionOfType, array $typeProperties)
    {
        Assertion::implementsInterface($relatedTypeClass, 'Prooph\Done\Process\\Type\Type');
        foreach($typeProperties as $propertyOfType) Assertion::isInstanceOf($propertyOfType, 'Prooph\Done\Process\Type\PrototypeProperty');

        $this->relatedTypeClass = $relatedTypeClass;
        $this->descriptionOfType = $descriptionOfType;
        $this->typeProperties = $typeProperties;

        PrototypeRegistry::registerPrototype($this);
    }

    /**
     * @return string class name of the related type
     */
    public function of()
    {
        return $this->relatedTypeClass;
    }

    /**
     * @return Description
     */
    public function typeDescription()
    {
        return $this->descriptionOfType;
    }

    /**
     * @return PrototypeProperty[]
     */
    public function typeProperties()
    {
        return $this->typeProperties;
    }
}
 