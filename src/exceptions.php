<?php

namespace Pramda;

class Exception extends \Exception
{

    /**
     * @param $collection
     *
     * @throws \InvalidArgumentException
     */
    public static function assertArray($collection)
    {
        if (!is_array($collection)) {
            throw new \InvalidArgumentException("Argument is not an array");
        }
    }

    /**
     * @param callable $callable
     *
     * @throws \InvalidArgumentException
     */
    public static function assertCallable($callable)
    {
        // Is it a callable?
        if (is_callable($callable)) {
            return;
        }
        // Is it an array that represents a callable?
        if (is_array($callable)) {
            if (method_exists($callable[0], $callable[1])) {
                return;
            }
        }
        // Is it a string that represents a callable
        if (is_string($callable)) {
            // of an object?
            if (strpos($callable, '::') !== FALSE) {
                $tmp = explode('::', $callable);
                self::assertCallable($tmp);

                return;
            } else {
                // or of a plain function?
                if (function_exists($callable)) {
                    return;
                }

                // Support for implicit P function
                if (is_callable(['P', $callable])) {
                    return;
                }
            }
        }

        throw new \InvalidArgumentException("Argument is not a callable");
    }

    /**
     * @param int|float $number
     *
     * @throws \InvalidArgumentException
     */
    public static function assertInteger($number)
    {
        if (!is_integer($number)) {
            throw new \InvalidArgumentException("Argument is not an integer");
        }
    }

    /**
     * @param mixed $collection
     *
     * @throws \InvalidArgumentException
     */
    public static function assertList($collection)
    {
        if (
            !($collection instanceof \Traversable) &&
            !is_array($collection) &&
            !($collection instanceof \Generator)
        ) {
            throw new \InvalidArgumentException("Argument is not a collection");
        }
    }

    /**
     * @param int|float $number
     *
     * @throws \InvalidArgumentException
     */
    public static function assertNonZero($number)
    {
        self::assertNumber($number);
        if ($number === 0) {
            throw new \InvalidArgumentException("Argument is a zero");
        }
    }

    /**
     * @param int|float $number
     *
     * @throws \InvalidArgumentException
     */
    public static function assertNumber($number)
    {
        if (!is_numeric($number)) {
            throw new \InvalidArgumentException("Argument is not a number");
        }
    }

    /**
     * @param int|float $number
     *
     * @throws \InvalidArgumentException
     */
    public static function assertPositiveInteger($number)
    {
        self::assertInteger($number);
        if ($number < 1) {
            throw new \InvalidArgumentException("Argument is not positive");
        }
    }

    /**
     * @param int|float $number
     *
     * @throws \InvalidArgumentException
     */
    public static function assertPositiveIntegerOrZero($number)
    {
        self::assertInteger($number);
        if ($number < 0) {
            throw new \InvalidArgumentException("Argument is not (positive or zero)");
        }
    }

    /**
     * @param string $str
     * 
     * @throws \InvalidArgumentException
     */
    public static function assertString($str)
    {
        if (!is_string($str)) {
            throw new \InvalidArgumentException("Argument is not a string");
        }
    }
}
