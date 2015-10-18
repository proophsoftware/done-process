<?php
/**
 * This file is part of the prooph software processing toolbox.
 * (c) 2014-2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Prooph\Done\Process\Functional;

use Prooph\Done\Process\Functional\Iterator\MapIterator;
use Prooph\Done\Process\Message\Payload;
use Prooph\Done\Process\Type\AbstractCollection;
use Prooph\Done\Process\Type\CollectionType;
use Prooph\Done\Process\Type\Type;

/**
 * Class Func
 *
 * Util class to provide basic functions for payload manipulation
 *
 * @package Prooph\Done\Process\Functional
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class Func
{
    //////////////////////////////////////////////////////
    /////////////////  Func Utils  ///////////////////////
    //////////////////////////////////////////////////////
    /**
     * Returns the requested method of Func as a callable function
     *
     * @param string $func
     * @return callable
     */
    public static function get($func)
    {
        return function () use ($func) {
            $args = func_get_args();

            return Func::call($func, $args);
        };
    }

    /**
     * Initializes method of Func passed as first argument with provided arguments.
     * Pass null for arguments that you wanna skip even when you wanna skip the last argument!
     *
     * @example
     * <code>
     *   $doAnythingWithEach = Func::prepare('map', null, function($value) { return do_anything_with($value); });
     *   $result = $doAnythingWithEach([1,2,3]);
     * </code>
     */
    public static function prepare()
    {
        $orgArgs = func_get_args();

        if (count($orgArgs) < 2) {
            throw new \BadMethodCallException('Func::prepare requires at least two arguments. First should be the function to prepare followed by arguments for that function');
        }

        $func = array_shift($orgArgs);

        return function () use ($func, $orgArgs) {
            $additionalArgs = func_get_args();

            foreach ($orgArgs as $index => $arg) {
                if (is_null($arg)) {

                    $additionalArg = array_shift($additionalArgs);

                    if (is_null($additionalArg)) {
                        throw new \BadMethodCallException('Parameter mismatch detected for prepared function: ' . (string)$func);
                    }

                    $orgArgs[$index] = $additionalArg;
                }
            }

            return Func::call($func, $orgArgs);
        };
    }

    /**
     * Calls a method of Func with arguments provided as array
     *
     * @param string $func
     * @param array $args
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     * @return mixed
     */
    public static function call($func, array $args)
    {
        switch ($func) {
            case 'map':
                if (count($args) != 2) throw new \BadMethodCallException("Wrong parameter count for method Func::map");
                return self::map($args[0], $args[1]);
            case 'premap':
                if (count($args) != 2) throw new \BadMethodCallException("Wrong parameter count for method Func::premap");
                return self::premap($args[0], $args[1]);
            case 'to_data':
                if (count($args) != 1) throw new \BadMethodCallException("Wrong parameter count for method Func::to_data");
                return self::to_data($args[0]);
            default:
                throw new \InvalidArgumentException(sprintf('Unknown function name provided: %s', (string)$func));
        }
    }

    //////////////////////////////////////////////////////
    /////////////////  Assertions  ///////////////////////
    //////////////////////////////////////////////////////

    /**
     * @param $callback
     * @throws \InvalidArgumentException
     */
    public static function assert_callable($callback)
    {
        if (! is_callable($callback)) {
            throw new \InvalidArgumentException("Provided callback is not callable. It is of type " . gettype($callback));
        }
    }

    //////////////////////////////////////////////////////
    /////////////  Collection Methods  ///////////////////
    //////////////////////////////////////////////////////

    /**
     * Returns an array of values by mapping each in collection through the callback.
     * Arguments passed to callback are (value, key, collection)
     *
     * @param mixed $collection
     * @param callable $callback
     * @return array
     */
    public static function map($collection, $callback)
    {
        self::assert_callable($callback);

        $result = [];

        foreach ($collection as $key => $value) {
            $result[$key] = $callback($value, $key, $collection);
        }

        return $result;
    }



    /**
     * Applies callback to collection
     * The callback is applied before the collection calls its own logic
     * on the current item. This allows you to prepare an item (change structure f.e.)
     * so that the collection can translate the item to its type representation
     *
     * @param CollectionType $collection
     * @param $callback
     * @return CollectionType
     */
    public static function premap(CollectionType $collection, $callback)
    {
        if ($collection instanceof \OuterIterator) {
            $collection = $collection::fromNativeValue(new MapIterator($collection->getInnerIterator(), $callback));
        }

        return $collection;
    }

    /**
     * @param Type $type
     * @return mixed
     */
    public static function to_data(Type $type)
    {
        $jsonStr = json_encode($type);

        return json_decode($jsonStr, true);
    }
} 