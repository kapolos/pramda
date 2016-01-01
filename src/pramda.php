<?php
use Pramda\Exception as Exception;

require_once("exceptions.php");

/**
 * Toolkit for Practical Functional Programming in PHP
 *
 * @package Pramda
 */
class P
{

    /**
     * @return mixed|callable
     */
    public static function add()
    {
        $args = func_get_args();

        /**
         * Adds two numbers. Equivalent to `a + b` but curried.
         *
         * @category Math
         *
         * @param int|float a The first value.
         * @param int|float b The second value.
         *
         * @return int|float The result of `a + b`.
         * @throws Exception
         */
        $_add = function ($a, $b) {
            Exception::assertNumber($a);
            Exception::assertNumber($b);

            return $a + $b;
        };

        return call_user_func_array(self::curry2($_add), $args);
    }

    /**
     * @return mixed|callable
     */
    public static function all()
    {
        $args = func_get_args();

        /**
         * Returns `true` if all elements of the list match the predicate, `false` if there are any
         * that don't.
         * Function "breaks early"
         *
         * @category List
         *
         * @param callable        $callable The predicate function.
         * @param Generator|array $list     The array to consider.
         *
         * @return boolean `true` if the predicate is satisfied by every element, `false`
         *         otherwise.
         * @throws Exception
         */
        $_all = function ($callable, $list) {
            Exception::assertCallable($callable);
            Exception::assertList($list);

            foreach ($list as $item) {
                if (!self::apply($callable, [$item])) {
                    return FALSE;
                }
            }

            return TRUE;
        };

        return call_user_func_array(self::curry2($_all), $args);
    }

    /**
     * @return mixed|callable
     */
    public static function allPass()
    {
        $args = func_get_args();

        /**
         * Returns true exactly when all the supplied predicates are true.
         *
         * @category Logic
         *
         * @param Generator|array $predicates A list of predicate functions
         * @param Mixed           $value      Any arguments to pass into the predicates
         *
         * @return Callable A function that applies its arguments to each of
         *         the predicates, returning `true` if all are satisfied.
         * @throws Exception
         */
        $_allPass = function ($predicates, $value) {
            Exception::assertList($predicates);

            return self::all(
                function ($a) {
                    return !$a ? FALSE : TRUE;
                },
                self::map(function ($f) use ($value) {
                    return self::apply($f, [$value]);
                }, $predicates)
            );
        };

        return call_user_func_array(self::curry2($_allPass), $args);
    }

    /**
     * @return mixed|callable
     */
    public static function any()
    {
        $args = func_get_args();

        /**
         * Returns `true` if at least one of elements of the list match the predicate, `false`
         * otherwise.
         *
         * @category List
         *
         * @param callable        $callable The predicate function.
         * @param Generator|array $list     The list to consider.
         *
         * @return Boolean `true` if the predicate is satisfied by at least one element, `false`
         *         otherwise.
         * @throws Exception
         */
        $_any = function ($callable, $list) {
            Exception::assertCallable($callable);
            Exception::assertList($list);

            foreach ($list as $item) {
                if (self::apply($callable, [$item])) {
                    return TRUE;
                }
            }

            return FALSE;
        };

        return call_user_func_array(self::curry2($_any), $args);
    }

    /**
     * @return mixed|callable
     */
    public static function append()
    {
        $args = func_get_args();

        /**
         * Returns a new list containing the contents of the given list, followed by the given element.
         *
         * @category List
         *
         * @param mixed           $el   The new element
         * @param Generator|array $list The list
         *
         * @return Generator
         * @throws Exception
         */
        $_append = function ($el, $list) {
            Exception::assertList($list);

            foreach ($list as $key => $value) {
                yield $key => $value;
            }
            yield $el;
        };

        return call_user_func_array(self::curry2($_append), $args);
    }

    /**
     * @return mixed|callable
     */
    public static function appendTo()
    {
        $args = func_get_args();

        /**
         * Returns a new list containing the contents of the given list, followed by the given element.
         *
         * @category List
         *
         * @param Generator|array $list The list
         * @param mixed           $el   The new element
         *
         * @return Generator
         * @throws Exception
         */
        $_appendTo = function ($list, $el) {
            return self::append($el, $list);
        };

        return call_user_func_array(self::curry2($_appendTo), $args);
    }

    /**
     * @return mixed|callable
     */
    public static function apply()
    {
        $args = func_get_args();

        /**
         * Applies function fn to the argument list args. This is useful for creating a fixed-arity function from a
         * variadic function.
         *
         * @category Function
         *
         * @param callable        $callable
         * @param Generator|array $list
         *
         * @return mixed
         * @throws Exception
         */
        $_apply = function ($callable, $list) {
            Exception::assertCallable($callable);
            Exception::assertList($list);

            return call_user_func_array($callable, self::toArray($list));
        };

        return call_user_func_array(self::curry2($_apply), $args);
    }

    /**
     * @return mixed|callable
     */
    public static function chain()
    {
        $args = func_get_args();

        /*
         * Maps a function over a list and concatenates the results.
         *
         * @category List
         *
         * @param callable $callable
         * @param Generator|array $list
         *
         * @return mixed|callable
         * @throws Exception
         */
        $_chain = function ($callable, $list) {
            Exception::assertList($list);

            $results = self::map(function ($item) use ($callable) {
                return self::apply($callable, [$item]);
            }, $list);

            foreach ($results as $item) {
                if (is_array($item)) {
                    foreach ($item as $sub) {
                        yield $sub;
                    }
                } else {
                    yield $item;
                }
            }
        };

        return call_user_func_array(self::curry2($_chain), $args);
    }

    /**
     * Performs right-to-left function composition. The rightmost function may have any arity; the remaining functions
     * must be unary.
     *
     * @category Function
     *
     * @return callable
     */
    public static function compose()
    {
        $args = func_get_args();

        self::each(function ($arg) {
            Exception::assertCallable($arg);
        }, $args);

        return self::reduce(function ($acc, $arg) {
            return is_null($acc) ? $arg : function () use ($acc, $arg) {
                $argz = func_get_args();

                return self::apply($acc, [self::apply($arg, $argz)]);
            };
        }, NULL, $args);
    }

    /**
     * @return mixed|callable
     */
    public static function concat()
    {
        $args = func_get_args();

        /**
         * Returns a new array consisting of the elements of the first list followed by the elements
         * of the second.
         * Works with associative arrays. In case of key conflict, latest value prevents when you use toArray
         * Still, you need to be mindful about this behavior in a lazy evaluation context
         *
         * @category List
         *
         * @param Generator|array $a
         * @param Generator|array $b
         *
         * @return Generator
         * @throws Exception
         */
        $_concat = function ($a, $b) {
            Exception::assertList($a);
            Exception::assertList($b);

            foreach ($a as $key => $value) {
                if (is_int($key)) {
                    yield $value;
                } else {
                    yield $key => $value;
                }
            }
            foreach ($b as $key => $value) {
                if (is_int($key)) {
                    yield $value;
                } else {
                    yield $key => $value;
                }
            }
        };

        return call_user_func_array(self::curry2($_concat), $args);
    }

    /**
     * @return mixed|callable
     */
    public static function contains()
    {
        $args = func_get_args();

        $_contains = function ($needle, $list) {
            Exception::assertList($list);

            foreach ($list as $item) {
                if ($needle === $item) {
                    return TRUE;
                }
            }

            return FALSE;
        };

        return call_user_func_array(self::curry2($_contains), $args);
    }

    /**
     * @return callable
     */
    public static function converge()
    {
        $args = func_get_args();

        /**
         * Accepts a converging function and a list of branching functions and returns a new function. When invoked, this new function is applied to some arguments, each branching function is applied to those same arguments. The results of each branching function are passed as arguments to the converging function to produce the return value.
         *
         * @category Function
         *
         * @param callable $conv  The converging function
         * @param array    $funcs List of branching functions
         *
         * @return callable
         * @throws Exception
         */
        $_converge = function ($conv, $funcs) {
            Exception::assertCallable($conv);
            Exception::assertList($funcs);

            return function () use ($conv, $funcs) {
                $args = func_get_args();

                $flipped_apply = P::flip('P::apply', 2);
                $applyItemsOverFunctions = self::compose(
                    'P::toArray',
                    self::map($flipped_apply($args))
                );

                return self::apply($conv, $applyItemsOverFunctions($funcs));
            };
        };

        return call_user_func_array(self::curry2($_converge), $args);
    }

    /**
     * @return mixed|callable
     */
    public static function countBy()
    {
        $args = func_get_args();

        /**
         * Counts the elements of a list according to how many match each value of a key generated by the supplied function.
         * Returns an associative array mapping the keys produced by the supplied function to the number of occurrences in the list.
         *
         * @category Relation
         *
         * @param callable        $callable This function should return only string|int
         * @param Generator|array $list
         *
         * @return array
         * @throws Exception
         */
        $_countBy = function ($callable, $list) {
            Exception::assertList($list);
            Exception::assertCallable($callable);

            $out = [];
            foreach ($list as $item) {
                $value = (string)self::apply($callable, [$item]);
                if (!empty($out["$value"])) {
                    $out["$value"]++;
                } else {
                    $out["$value"] = 1;
                }
            }

            return $out;
        };

        return call_user_func_array(self::curry2($_countBy), $args);
    }

    /**
     * Returns a curried equivalent of the provided 2-arity function.
     *
     * @category Function
     *
     * @param $callable
     *
     * @return callable
     * @throws Exception
     */
    public static function curry2($callable)
    {
        Exception::assertCallable($callable);

        return function () use ($callable) {
            $args = func_get_args();

            switch (func_num_args()) {
                case 0:
                    throw new Exception("Invalid number of arguments");
                    break;
                case 1:
                    return function ($b) use ($args, $callable) {
                        return call_user_func_array($callable, [$args[0], $b]);
                    };
                    break;
                case 2:
                    return call_user_func_array($callable, $args);
                    break;
                default:
                    // Why? To support passing curried functions as parameters to functions that pass more that 2 parameters, like reduce
                    return call_user_func_array($callable, [$args[0], $args[1]]);
                    break;
            }
        };
    }

    /**
     * Returns a curried equivalent of the provided 3-arity function.
     *
     * @category Function
     *
     * @param $callable
     *
     * @return callable
     * @throws Exception
     */
    public static function curry3($callable)
    {
        Exception::assertCallable($callable);

        return function () use ($callable) {
            $args = func_get_args();

            switch (func_num_args()) {
                case 0:
                    throw new Exception("Invalid number of arguments");
                    break;
                case 1:
                    return self::curry2(function ($b, $c) use ($args, $callable) {
                        return call_user_func_array($callable, [$args[0], $b, $c]);
                    });
                    break;
                case 2:
                    return function ($c) use ($args, $callable) {
                        return call_user_func_array($callable, [$args[0], $args[1], $c]);
                    };
                    break;
                case 3:
                    return call_user_func_array($callable, $args);
                    break;
                default:
                    // Similarly to curry2()
                    return call_user_func_array($callable, [$args[0], $args[1], $args[2]]);
                    break;
            }
        };
    }

    /**
     * Wrapper for curry* functions.
     *
     * @category Function
     *
     * @param int      $arity
     * @param callable $callable
     *
     * @return callable
     */
    public static function curryN($arity, $callable)
    {
        switch ($arity) {
            case '2':
                return self::curry2($callable);
                break;
            case '3':
                return self::curry3($callable);
                break;
            default:
                throw new RuntimeException;
                break;
        }
    }

    /**
     * Decrements its argument
     *
     * @category Math
     *
     * @param int|float $a The value.
     *
     * @return int|float The result of (a - b).
     */
    public static function dec($a)
    {
        Exception::assertNumber($a);

        return $a - 1;
    }

    /**
     * @return mixed|callable
     */
    public static function divide()
    {
        $args = func_get_args();

        /**
         * Divides two numbers. Equivalent to `$a / $b`.
         *
         * @category Math
         *
         * @param int|float $a The first value.
         * @param int|float $b The second value.
         *
         * @return float The result of `$a / $b`.
         */
        $_divide = function ($a, $b) {
            Exception::assertNumber($a);
            Exception::assertNonZero($b);

            return $a / $b;
        };

        return call_user_func_array(self::curry2($_divide), $args);
    }

    /**
     * @return mixed|callable
     */
    public static function each()
    {
        $args = func_get_args();

        /**
         * Iterate over an input list, calling a provided function fn for each element in the list.
         * fn is called with 2 parameters - the current list item and the item's index.
         *
         * @category List
         *
         * @param callable        $callable
         * @param Generator|array $list
         *
         * @throws Exception
         */
        $_each = function ($callable, $list) {
            Exception::assertCallable($callable);
            Exception::assertList($list);

            foreach ($list as $index => $item) {
                self::apply($callable, [$item, $index]);
            }
        };

        return call_user_func_array(self::curry2($_each), $args);
    }

    /**
     * @return mixed|callable
     */
    public static function eq()
    {
        $args = func_get_args();

        /**
         * Tests if two items are equal (using the === operator)
         *
         * @category Relation
         *
         * @param mixed $a
         * @param mixed $b
         *
         * @return bool
         */
        $_eq = function ($a, $b) {
            return $a === $b;
        };

        return call_user_func_array(self::curry2($_eq), $args);
    }

    /**
     * @return mixed|callable
     */
    public static function eqBy()
    {
        $args = func_get_args();

        /**
         * Takes a function and two values in its domain and returns true if the values map to the same value (===) operator
         *
         * @category Relation
         *
         * @param callable $callable The function to apply on the parameters
         * @param mixed    $a
         * @param mixed    $b
         *
         * @return bool
         */
        $_eqBy = function ($callable, $a, $b) {
            return self::apply($callable, [$a]) === self::apply($callable, [$b]);
        };

        return call_user_func_array(self::curry3($_eqBy), $args);
    }

    public static function file($filename)
    {
        if (!$handle = fopen($filename, 'r')) {
            yield;
        }

        while (FALSE !== $line = fgets($handle)) {
            yield $line;
        }

        fclose($handle);
    }

    /**
     * @return mixed|callable
     */
    public static function filter()
    {
        $args = func_get_args();

        /**
         * Returns a new list containing only those items that match a given predicate function. The predicate function is
         * passed one argument: (value).
         *
         * @category List
         *
         * @param callable  $callable (value, key)
         * @param Generator $list
         *
         * @return Generator
         * @throws Exception
         */
        $_filter = function ($callable, $list) {
            Exception::assertCallable($callable);
            Exception::assertList($list);

            foreach ($list as $key => $value) {
                if (self::apply($callable, [$value, $key])) {
                    yield $key => $value;
                }
            }
        };

        return call_user_func_array(self::curry2($_filter), $args);
    }

    /**
     * Returns a new list by pulling every item out of it (and all its sub-arrays) and putting them in a new array,
     * depth-first.
     *
     * @category List
     *
     * @param Generator|array $list
     *
     * @return Generator|array $list
     * @throws Exception
     */
    public static function flatten($list)
    {
        Exception::assertList($list);

        foreach ($list as $item) {
            if (is_array($item)) {
                // Since yield from is PHP 7+ only, we do this trick
                // Credit: zilvinas at kuusas dot lt (https://secure.php.net/manual/en/language.generators.syntax.php)
                foreach (self::flatten($item) as $sub) {
                    yield $sub;
                }
            } else {
                yield $item;
            }
        }
    }

    /**
     * Returns a new function much like the supplied one, except that the first two arguments' order is reversed.
     *
     * @category Function
     *
     * @param callable $callable
     * @param int|null $arity
     *
     * @return callable
     * @throws Exception
     */
    public static function flip($callable, $arity = NULL)
    {
        if ($arity === NULL) {
            $arity = self::_getArity($callable);
        }

        return self::curryN($arity, function () use ($callable) {
            $args = func_get_args();

            return self::apply($callable, self::concat([$args[1], $args[0]], self::tail(self::tail($args))));
        });
    }

    /**
     * @return mixed|callable
     */
    public static function gt()
    {
        $args = func_get_args();

        /**
         * Returns true if the first parameter is greater than the second.
         *
         * @category Math
         *
         * @param int|float $a
         * @param int|float $b
         *
         * @return Boolean $a > $b
         */
        $_gt = function ($a, $b) {
            Exception::assertNumber($a);
            Exception::assertNumber($b);

            return $a > $b ? TRUE : FALSE;
        };

        return call_user_func_array(self::curry2($_gt), $args);
    }

    /**
     * @return mixed|callable
     */
    public static function gte()
    {
        $args = func_get_args();

        /**
         * Returns true if the first parameter is greater than or equal to the second.
         *
         * @category Math
         *
         * @param int|float $a
         * @param int|float $b
         *
         * @return Boolean $a >= $b
         */
        $_gte = function ($a, $b) {
            Exception::assertNumber($a);
            Exception::assertNumber($b);

            return $a >= $b ? TRUE : FALSE;
        };

        return call_user_func_array(self::curry2($_gte), $args);
    }

    /**
     * Returns the first element of a list, NULL is empty list
     * If the list is an associative array, it will return an array with 1 key=>value
     *
     * @category List
     *
     * @param Generator|array $list
     *
     * @return mixed|null
     * @throws Exception
     */
    public
    static function head($list)
    {
        $items = self::take(1, $list);
        if (empty($items)) {
            return NULL;
        }

        foreach ($items as $key => $value) {
            if (is_int($key)) {
                return $value;
            } else {
                return [$key => $value];
            }
        }
    }

    /**
     * A function that does nothing but return the parameter supplied to it. Good as a default
     * or placeholder function.
     *
     * @category Function
     *
     * @param $a
     *
     * @return mixed
     */
    public static function identity($a)
    {
        return $a;
    }

    /**
     * Increments its argument
     *
     * @category Math
     *
     * @param Number $a The value.
     *
     * @return Number The result of (a - b).
     */
    public static function inc($a)
    {
        Exception::assertNumber($a);

        return $a + 1;
    }

    public static function join()
    {
        $args = func_get_args();

        /**
         * Returns a string made by inserting the separator between each element and concatenating all the elements into a
         * single string.
         *
         * @category List
         *
         * @param $separator
         * @param $list
         *
         * @return mixed
         * @throws Exception
         */
        $_join = function ($separator, $list) {
            Exception::assertString($separator);
            Exception::assertList($list);

            return self::reduce(function ($acc, $item) use ($separator) {
                return empty($acc) ? $item : $acc . $separator . $item;
            }, '', $list);
        };

        return call_user_func_array(self::curry2($_join), $args);
    }

    /**
     * Returns the last element from a list or NULL is the list is empty
     * If the list is an associative array, it will return an array with 1 key=>value
     *
     * @category List
     *
     * @param Generator|array $list
     *
     * @return mixed|null
     */
    public static function last($list)
    {
        $out = NULL;
        foreach ($list as $key => $value) {
            if (is_int($key)) {
                $out = $value;
            } else {
                $out = [$key => $value];
            }
        }

        return $out;
    }

    /**
     * @return mixed|callable
     */
    public static function lt()
    {
        $args = func_get_args();

        /**
         * Returns true if the first parameter is less than the second.
         *
         * @category Math
         *
         * @param int|float $a
         * @param int|float $b
         *
         * @return boolean
         */
        $_lt = function ($a, $b) {
            return $a < $b ? TRUE : FALSE;
        };

        return call_user_func_array(self::curry2($_lt), $args);
    }

    /**
     * @return mixed|callable
     */
    public static function lte()
    {
        $args = func_get_args();

        /**
         * Returns true if the first parameter is less than the second.
         *
         * @category Math
         *
         * @param int|float $a
         * @param int|float $b
         *
         * @return boolean
         */
        $_lte = function ($a, $b) {
            return $a <= $b ? TRUE : FALSE;
        };

        return call_user_func_array(self::curry2($_lte), $args);
    }

    /**
     * @return mixed|callable
     */
    public static function map()
    {
        $args = func_get_args();

        /**
         * Returns a new list, constructed by applying the supplied function to every element of the supplied list.
         * The supplied function takes the current item and its index position as arguments
         *
         * @param callable        $callable (value, key)
         * @param Generator|array $list
         *
         * @category List
         *
         * @return Generator
         * @throws Exception
         */
        $_map = function ($callable, $list) {
            Exception::assertCallable($callable);
            Exception::assertList($list);

            foreach ($list as $key => $value) {
                yield self::apply($callable, [$value, $key]);
            }
        };

        return call_user_func_array(self::curry2($_map), $args);
    }

    /**
     * @return mixed|callable
     */
    public static function mathMod()
    {
        $args = func_get_args();

        /**
         * mathMod behaves like the modulo operator should mathematically, unlike the `%`
         * operator (and by extension, R.modulo). So while "-17 % 5" is -2,
         * mathMod(-17, 5) is 3
         *
         * @category Math
         *
         * @param int $a The dividend
         * @param int $b the modulus
         *
         * @return int The result of `a (mod b)`.
         */
        $_mathMod = function ($a, $b) {
            Exception::assertInteger($a);
            Exception::assertPositiveInteger($b);

            return (($a % $b) + $b) % $b;
        };

        return call_user_func_array(self::curry2($_mathMod), $args);
    }

    /**
     * Determines the largest of a list of numbers. NULL if the list is empty.
     *
     * @category Math
     *
     * @param Generator|array $list A list of numbers
     *
     * @return int|float The biggest number in the list.
     */
    public static function max($list)
    {
        Exception::assertList($list);

        $max = NULL;
        foreach ($list as $item) {
            if ($max == NULL) {
                $max = $item;
                continue;
            }
            if ($item > $max) {
                $max = $item;
            }
        }

        return $max;
    }

    /**
     * @return mixed|callable
     */
    public static function maxBy()
    {
        $args = func_get_args();

        /**
         * Determines the largest of a list of items as determined by pairwise comparisons from the supplied comparator
         *
         * @category Math
         *
         * @param Callable        $callable A comparator function for elements in the list
         * @param Generator|array $list     A list of comparable elements
         *
         * @return mixed The greatest element in the list. `null` if the list is empty.
         */
        $_maxBy = function ($callable, $list) {
            Exception::assertCallable($callable);
            Exception::assertList($list);

            return self::reduce(function ($acc, $arg) use ($callable) {
                if ($acc === NULL) return $arg;

                return self::apply($callable, [$arg, $acc]) ? $arg : $acc;
            }, NULL, $list);
        };

        return call_user_func_array(self::curry2($_maxBy), $args);
    }

    /**
     * @return mixed|callable
     */
    public static function merge()
    {
        $args = func_get_args();

        /**
         * Curried version of array_merge - 2 arrays
         *
         * @category List
         *
         * @param Generator|array $a
         * @param Generator|array $b
         *
         * @return array
         * @throws Exception
         */
        $_merge = function ($a, $b) {
            Exception::assertList($a);
            Exception::assertList($b);

            return array_merge(P::toArray($a), P::toArray($b));
        };

        return call_user_func_array(self::curry2($_merge), $args);
    }

    /**
     * Just like array_merge but you can also pass generator instances
     *
     * @category List
     *
     * @param Generator|array $list A list of Generator
     *
     * @return array
     * @throws Exception
     */
    public static function mergeAll($list)
    {
        Exception::assertList($list);

        $out = [];
        foreach ($list as $item) {
            $out = array_merge($out, P::toArray($item));
        }

        return $out;
    }

    /**
     * Determines the smallest of a list of numbers
     *
     * @category Math
     *
     * @param Generator|array $list A list of numbers
     *
     * @return int|float The smallest number in the list.
     */
    public static function min($list)
    {
        Exception::assertList($list);

        $min = NULL;
        foreach ($list as $item) {
            if ($min == NULL) {
                $min = $item;
                continue;
            }
            if ($item < $min) {
                $min = $item;
            }
        }

        return $min;
    }

    /**
     * @return mixed|callable
     */
    public static function minBy()
    {
        $args = func_get_args();

        /**
         * Determines the smallest of a list of items as determined by pairwise comparisons from the supplied comparator
         *
         * @category Math
         *
         * @param Callable        $callable A comparator function for elements in the list
         * @param Generator|array $list     A list of comparable elements
         *
         * @return mixed The greatest element in the list. `null` if the list is empty.
         */
        $_minBy = function ($callable, $list) {
            Exception::assertCallable($callable);
            Exception::assertList($list);

            return self::reduce(function ($acc, $arg) use ($callable) {
                if ($acc === NULL) return $arg;

                return !self::apply($callable, [$arg, $acc]) ? $arg : $acc;
            }, NULL, $list);
        };

        return call_user_func_array(self::curry2($_minBy), $args);
    }

    /**
     * @return mixed|callable
     */
    public static function modulo()
    {
        $args = func_get_args();

        /**
         * Divides the second parameter by the first and returns the remainder.
         * Note that this functions preserves the PHP-style behavior for
         * modulo. For mathematical modulo see `mathMod`
         *
         * @category Math
         *
         * @param int $a The value to the divide.
         * @param int $b The pseudo-modulus
         *
         * @return int The result of `a % b`.
         */
        $_modulo = function ($a, $b) {
            Exception::assertInteger($a);
            Exception::assertInteger($b);

            return $a % $b;
        };

        return call_user_func_array(self::curry2($_modulo), $args);
    }

    /**
     * @return mixed|callable
     */
    public static function multiply()
    {
        $args = func_get_args();

        /**
         * Multiplies two numbers. Equivalent to `a * b` but curried.
         *
         * @category Math
         *
         * @param int|float $a The first value.
         * @param int|float $b The second value.
         *
         * @return int|float The result of `a * b`.
         */
        $_multiply = function ($a, $b) {
            Exception::assertNumber($a);
            Exception::assertNumber($b);

            return $a * $b;
        };

        return call_user_func_array(self::curry2($_multiply), $args);
    }

    /**
     * @return mixed|callable
     */
    public static function nd()
    {
        $args = func_get_args();

        /**
         * Logical And but curried.
         * And is a reserved keyword on PHP so... `nd`
         *
         * @category Logic
         *
         * @param Mixed $a Any value
         * @param Mixed $b Any value
         *
         * @return Boolean The result of `a && b`.
         */
        $_and = function ($a, $b) {
            return $a && $b;
        };

        return call_user_func_array(self::curry2($_and), $args);
    }

    /**
     * Negates its argument.
     *
     * @category Math
     *
     * @param int|float $a
     *
     * @return int|float
     */
    public static function negate($a)
    {
        Exception::assertNumber($a);

        return -$a;
    }

    public static function nth()
    {
        $args = func_get_args();

        /**
         * Returns the nth element in a list. First position is 0.
         * With associative arrays, it will not preserve the key
         *
         * @category List
         *
         * @param int $n List index
         * @param     $list
         *
         * @return mixed|NULL
         */
        $_nth = function ($n, $list) {
            Exception::assertPositiveIntegerOrZero($n);
            Exception::assertList($list);

            $count = 0;
            foreach ($list as $item) {
                if ($count === $n) {
                    return $item;
                }
                $count++;
            }

            return NULL;
        };

        return call_user_func_array(self::curry2($_nth), $args);
    }

    /**
     * Wraps any object inside an Array.
     *
     * @category Function
     *
     * @param  mixed $el
     *
     * @return array
     */
    public static function of($el)
    {

        return [$el];
    }

    /**
     * @return mixed|callable
     */
    public static function partition()
    {
        $args = func_get_args();

        /**
         * Takes a predicate and a list and returns the pair of lists of elements which do and do not satisfy the predicate, respectively.
         *
         * @category List
         *
         * @param callable        $predicate
         * @param Generator|array $list
         *
         * @return array
         * @throws Exception
         */
        $_partition = function ($predicate, $list) {
            Exception::assertList($list);
            Exception::assertCallable($predicate);

            return self::reduce(function ($acc, $item) use ($predicate) {
                if (self::apply($predicate, [$item]) === TRUE) {
                    array_push($acc[0], $item);
                } else {
                    array_push($acc[1], $item);
                }

                return $acc;
            }, [[], []], $list);
        };

        return call_user_func_array(self::curry2($_partition), $args);
    }

    /**
     * @return mixed|callable
     */
    public static function pipe()
    {
        $args = func_get_args();

        return self::apply('P::compose', self::reverse($args));
    }

    /**
     * @return mixed|callable
     */
    public static function pluck()
    {
        $args = func_get_args();

        /**
         * Returns a new list by plucking the same named property off all objects in the list supplied.
         *
         * @category List
         *
         * @param string          $key
         * @param Generator|array $records
         *
         * @return Generator
         * @throws Exception
         */
        $_pluck = function ($key, $records) {
            Exception::assertList($records);

            foreach ($records as $item) {
                if (isset($item[$key])) {
                    yield $item[$key];
                }
            }
        };

        return call_user_func_array(self::curry2($_pluck), $args);
    }

    /**
     * @return mixed|callable
     */
    public static function prepend()
    {
        $args = func_get_args();

        /**
         * Returns a new list with the given element at the front, followed by the contents of the
         * list.
         *
         * @category List
         *
         * @param mixed           $el   The new element
         * @param Generator|array $list The list
         *
         * @return Generator
         * @throws Exception
         */
        $_prepend = function ($el, $list) {
            Exception::assertList($list);

            return self::concat(self::of($el), $list);
        };

        return call_user_func_array(self::curry2($_prepend), $args);
    }

    /**
     * @return mixed|callable
     */
    public static function prependTo()
    {
        $args = func_get_args();

        /**
         * Returns a new list with the given element at the front, followed by the contents of the
         * list.
         *
         * @category List
         *
         * @param Generator $list The list
         * @param mixed     $el   The new element
         *
         * @return Generator|array
         * @throws Exception
         */
        $_prependTo = function ($list, $el) {
            Exception::assertList($list);

            return self::prepend($el, $list);
        };

        return call_user_func_array(self::curry2($_prependTo), $args);
    }

    /**
     * Multiplies together all the elements of a list.
     *
     * @category Math
     *
     * @param Generator|array $list A list of numbers
     *
     * @return int|float The product of all the numbers in the list.
     */
    public static function product($list)
    {
        Exception::assertList($list);

        return self::reduce('P::multiply', 1, $list);
    }

    /**
     * @return mixed|callable
     */
    public static function prop()
    {
        $args = func_get_args();

        /**
         * Over an object, it returns the indicated property of that object, if it exists or NULL
         * Over an array, it returns the indicated key of that array, if it exists or NULL. Due to how arrays are
         * implemented in PHP, this works on indexed arrays as well
         *
         * @category Object
         *
         * @example  P::_prop('x', ["x" => 100]); // 100
         * @example  P::_prop('0', [100]); // 100
         *
         * @param $key
         * @param $item
         *
         * @return null
         */
        $_prop = function ($key, $item) {

            if (is_array($item)) {
                if (array_key_exists($key, $item)) {
                    return $item[$key];
                }
            }
            if (is_object($item)) {
                if (property_exists($item, $key)) {
                    return $item->$key;
                }
            }

            return NULL;
        };

        return call_user_func_array(self::curry2($_prop), $args);
    }

    /**
     * @return mixed|callable
     */
    public static function propOf()
    {
        $args = func_get_args();

        /**
         * Like prop() but with reverse arguments order
         *
         * @category Object
         *
         * @param $item
         * @param $key
         *
         * @return mixed
         */
        $_propOf = function ($item, $key) {
            return self::apply(self::flip('P::prop', 2), func_get_args());
        };

        return call_user_func_array(self::curry2($_propOf), $args);
    }

    /**
     * @return mixed|callable
     */
    public static function propOr()
    {
        $args = func_get_args();

        /**
         * Like prop() but with default value instead of returning NULL
         *
         * @param $key
         * @param $default
         * @param $item
         *
         * @return null
         */
        $__propOr = function ($key, $default, $item) {
            $prop = self::prop($key, $item);

            return is_null($prop) ? $default : $prop;
        };

        return call_user_func_array(self::curry3($__propOr), $args);
    }

    /**
     * @return mixed|callable
     */
    public static function props()
    {
        $args = func_get_args();

        /**
         * Acts as multiple prop() calls
         *
         * @category object
         *
         * @param $keys
         * @param $item
         *
         * @return Generator|array
         * @throws Exception
         */
        $_props = function ($keys, $item) {
            Exception::assertList($keys);

            return self::map(function ($key) use ($item) {
                return self::prop($key, $item);
            }, $keys);
        };

        return call_user_func_array(self::curry2($_props), $args);
    }

    /**
     * @return mixed|callable
     */
    public static function reduce()
    {
        $args = func_get_args();

        /**
         * Returns a single item by iterating through the list, successively calling the iterator
         * function and passing it an accumulator value and the current value from the array, and
         * then passing the result to the next call.
         *
         * The iterator function receives three values: (accumulator, value, key)
         *
         * @category List
         *
         * @param callable        $callable ($acc, $value)
         * @param mixed           $acc
         * @param Generator|array $list
         *
         * @return mixed
         */
        $_reduce = function ($callable, $acc, $list) {
            Exception::assertList($list);
            Exception::assertCallable($callable);

            foreach ($list as $key => $value) {
                $acc = self::apply($callable, [$acc, $value, $key]);
            }

            return $acc;
        };

        return call_user_func_array(self::curry3($_reduce), $args);
    }

    /**
     * Like array_reverse but accepts a generator as well
     *
     * #category List
     *
     * @param Generator|array $list
     *
     * @return array
     * @throws Exception
     */
    public static function reverse($list)
    {
        Exception::assertList($list);

        return array_reverse(self::toArray($list));
    }

    /**
     * @return mixed|callable
     */
    public static function set()
    {
        $args = func_get_args();

        /**
         * Sets value of associative array by key
         *
         * @param string $prop
         * @param mixed  $val
         * @param array  $item
         *
         * @return array
         */
        $_setProp = function ($prop, $val, $item) {
            $item[$prop] = $val;

            return $item;
        };

        return call_user_func_array(self::curry3($_setProp), $args);
    }

    /**
     * Returns the number of elements in the list
     * Works with objects
     *
     * @param Generator|array $list
     *
     * @return int
     */
    public static function size($list)
    {

        $count = 0;
        foreach ($list as $item) {
            $count++;
        }

        return $count;
    }

    /**
     * @return mixed|callable
     */
    public static function slice()
    {
        $args = func_get_args();

        /**
         * Returns the elements of the given list from start (inclusive) to end (exclusive).
         * Curried version of array_slice
         *
         * @category List
         *
         * @param int   $start
         * @param int   $end
         * @param array $arr
         *
         * @return array
         * @throws Exception
         */
        $_slice = function ($start, $end, $arr) {
            $arr = P::toArray($arr);

            Exception::assertArray($arr);
            Exception::assertInteger($start);

            if ($end === NULL) {
                $end = count($arr);
            }
            Exception::assertInteger($end);

            return array_slice($arr, $start, $end - $start);
        };

        return call_user_func_array(self::curry3($_slice), $args);
    }

    /**
     * @return mixed|callable
     */
    public static function sort()
    {
        $args = func_get_args();

        /**
         * Returns a sorted copy of the list, applying the provided function to each element before comparison
         *
         * @param callable        $callable
         * @param Generator|array $arr
         *
         * @return array
         * @throws Exception
         */
        $_sort = function ($callable, $arr) {
            Exception::assertList($arr);
            Exception::assertCallable($callable);

            $arr = self::toArray($arr);

            $comparator = function ($a, $b) use ($callable) {
                $aa = self::apply($callable, [$a]);
                $bb = self::apply($callable, [$b]);
                $delta = $aa - $bb;

                // Caution - Comparator MUST return integers
                // See: https://secure.php.net/manual/en/function.usort.php
                if ($delta > 0) {
                    return 1;
                } elseif ($delta < 0) {
                    return -1;
                }

                return 0;
            };

            if (self::_isAssociativeArray($arr)) {
                uasort($arr, $comparator);
            } else {
                usort($arr, $comparator);
            }

            return $arr;
        };

        return call_user_func_array(self::curry2($_sort), $args);
    }

    /**
     * @return mixed|callable
     */
    public static function split()
    {
        $args = func_get_args();

        /**
         * Explode() wrapper
         *
         * @param $separator
         * @param $string
         *
         * @return array
         */
        $_split = function ($separator, $string) {
            return explode($separator, $string);
        };

        return call_user_func_array(self::curry2($_split), $args);
    }

    /**
     * @return mixed|callable
     */
    public static function subtract()
    {
        $args = func_get_args();

        /**
         * Subtracts two numbers. Equivalent to `a - b` but curried.
         *
         * @category Math
         *
         * @param int|float $a The first value.
         * @param int|float $b The second value.
         *
         * @return Number The result of `$a - $b`.
         */
        $_subtract = function ($a, $b) {
            Exception::assertNumber($a);
            Exception::assertNumber($b);

            return $a - $b;
        };

        return call_user_func_array(self::curry2($_subtract), $args);
    }

    /**
     * Adds together all the elements of a list. Returns 0 for an empty list.
     *
     * @category Math
     *
     * @param Generator|array $list A list of numbers
     *
     * @return int|float The sum of all the numbers in the list.
     */
    public static function sum($list)
    {
        Exception::assertList($list);

        return self::reduce('P::add', 0, $list);
    }

    /**
     * Returns all but the first element of a list.
     * Preserves keys on associative (non-numerical key) arrays
     *
     * @category List
     *
     * @param Generator|array $list
     *
     * @return Generator
     */
    public static function tail($list)
    {
        Exception::assertList($list);

        $skip = TRUE;
        foreach ($list as $key => $value) {
            if ($skip) {
                $skip = FALSE;
                continue;
            }
            if (is_int($key)) {
                yield $value;
            } else {
                yield $key => $value;
            }
        }
    }

    /**
     * @return mixed|callable
     */
    public static function take()
    {
        $args = func_get_args();

        /**
         * Returns the first n elements of the given list
         * Associative array support: For each item, if the key is non-integer, it returns [$key=>value]
         *
         * @category List
         *
         * @param $count
         * @param $list
         *
         * @return Generator
         * @throws Exception
         */
        $_take = function ($count, $list) {
            Exception::assertList($list);

            $i = 0;
            foreach ($list as $key => $value) {
                if ($i++ < $count) {
                    if (is_int($key)) {
                        yield $value;
                    } else {
                        yield $key => $value;
                    }
                } else {
                    break;
                }
            }
        };

        return call_user_func_array(self::curry2($_take), $args);
    }

    /**
     * Returns the last item of a list or NULL if the list is empty
     *
     * @category List
     *
     * @param Generator|array $list
     *
     * @return Generator
     * @throws Exception
     */
    public static function takeLast($list)
    {
        Exception::assertList($list);

        $out = NULL;
        foreach ($list as $key => $value) {
            if (is_int($key)) {
                $out = $value;
            } else {
                $out = [$key => $value];
            }
        }

        return $out;
    }

    public static function takeWhile()
    {
        $args = func_get_args();

        /**
         * Returns items from a list as long as the supplied function returns true for each one.
         * The supplied function takes the item's value/key as arguments.
         * For associative arrays, the non-numerical keys are preserved
         *
         * @param callable        $callable
         * @param Generator|array $list
         *
         * @return \Generator
         * @throws Exception
         */
        $_takeWhile = function ($callable, $list) {
            Exception::assertList($list);

            foreach ($list as $key => $value) {
                if (self::apply($callable, [$value, $key]) === TRUE) {
                    if (is_int($key)) {
                        yield $value;
                    } else {
                        yield $key => $value;
                    }
                }
            }
        };

        return call_user_func_array(self::curry2($_takeWhile), $args);
    }

    /**
     * Exhausts a generator or returns the list as is if it was not a generator
     * Associative array support: If a generator -> For each item, if the key is non-integer, it returns [$key=>value]
     *
     * @category List
     *
     * @param $list
     *
     * @return array
     */
    public static function toArray($list)
    {
        Exception::assertList($list);

        if (!($list instanceof \Generator)) {
            return $list;
        }

        $out = [];
        foreach ($list as $key => $value) {
            if (is_int($key)) {
                $out[] = $value;
            } else {
                $out[$key] = $value;
            }
        }

        return $out;
    }

    /**
     * Trampoline for tail-recursion to prevent PHP from blowing the stack
     *
     * @param $callable
     *
     * @return Closure
     * @throws Exception
     */
    public static function trampoline($callable)
    {
        // Based on: https://gist.github.com/pkriete/2425817
        // How to do tail recursion: http://abdulapopoola.com/2014/08/04/understanding-tail-recursion/
        Exception::assertCallable($callable);

        $acc = [];
        $block = FALSE;

        return function () use ($callable, &$acc, &$block) {
            $acc[] = func_get_args();
            $ret = NULL;

            if (!$block) {
                $block = !$block;
                while (!empty($acc)) {
                    $ret = self::apply($callable, array_shift($acc));
                }
                $block = !$block;
            }

            return $ret;
        };
    }

    /**
     * Takes a function, which takes a single array argument, and returns
     * a function which:
     *
     *   - takes any number of positional arguments;
     *   - passes these arguments to `fn` as an array; and
     *   - returns the result.
     *
     * @category Function
     *
     * @param callable $callable
     *
     * @return callable
     * @throws \Exception
     */
    public static function unapply($callable)
    {
        Exception::assertCallable($callable);

        return function () use ($callable) {
            $args = func_get_args();

            return call_user_func($callable, $args);
        };
    }

    /**
     * Wraps a function of any arity (including nullary) in a function that accepts exactly 1 parameter. Any extraneous
     * parameters will not be passed to the supplied function. This is useful for wrapping native PHP functions that
     * expect only one parameter when using map/each/reduce that send 2 parameters
     *
     * @param $callable
     *
     * @return Closure
     * @throws Exception
     */
    public static function unary($callable)
    {
        Exception::assertCallable($callable);

        return function () use ($callable) {
            $args = func_get_args();
            if (empty($args)) {
                $args[0] = NULL;
            }

            return self::apply($callable, [$args[0]]);
        };
    }

    /**
     * Returns a new list containing only one copy of each element in the original list.
     * Equality is strict here (===)
     *
     * @category List
     *
     * @param Generator|array $list
     *
     * @return array
     * @throws Exception
     */
    public static function uniq($list)
    {
        Exception::assertList($list);

        return array_values(array_unique(P::toArray($list)));
    }

    public static function unnest($list)
    {
        Exception::assertList($list);

        return self::apply(P::chain('P::identity'), [$list]);
    }

    /**
     * Returns all the values of a list.
     *
     * @category List
     *
     * @param    Generator|array
     *
     * @return Generator
     * @throws Exception
     */
    public static function values($list)
    {
        Exception::assertList($list);

        foreach ($list as $key => $value) {
            yield $value;
        }
    }

    /**
     * @return mixed|callable
     */
    public static function zip()
    {
        $args = func_get_args();

        /**
         * Creates a new list out of the two supplied by pairing up equally-positioned items from both lists. The returned list is truncated to the length of the shorter of the two input lists.
         *
         * @param Generator|array $a
         * @param Generator|array $b
         *
         * @category List
         *
         * @return Generator
         * @throws Exception
         */
        $_zip = function ($a, $b) {
            Exception::assertList($a);
            Exception::assertList($b);

            return self::zipWith(function ($a, $b) {
                return [$a, $b];
            }, $a, $b);
        };

        return call_user_func_array(self::curry2($_zip), $args);
    }

    /**
     * @return mixed|callable
     */
    public static function zipAssoc()
    {
        $args = func_get_args();

        /**
         * Creates a list of associative arrays. Keys come from the first provided list and values from the second one.
         *
         * @param Generator|array $a
         * @param Generator|array $b
         *
         * @return Generator
         * @throws Exception
         */
        $_zipAssoc = function ($a, $b) {
            Exception::assertList($a);
            Exception::assertList($b);

            if (is_array($a)) {
                $a = new \ArrayIterator($a);
            }
            if (is_array($b)) {
                $b = new \ArrayIterator($b);
            }

            if (!empty($a->current())) {
                while ($a->valid() && $b->valid()) {
                    yield $a->current() => $b->current();
                    $a->next();
                    $b->next();
                }
            }
        };

        return call_user_func_array(self::curry2($_zipAssoc), $args);
    }

    /**
     * @return mixed|callable
     */
    public static function zipWith()
    {
        $args = func_get_args();

        /**
         * Like zip but applies the callable to each value before yielding
         *
         * @param callable        $callable
         * @param Generator|array $a
         * @param Generator|array $b
         *
         * @return \Generator
         * @throws Exception
         */
        $_zipWith = function ($callable, $a, $b) {
            Exception::assertCallable($callable);
            Exception::assertList($a);
            Exception::assertList($b);

            if (is_array($a)) {
                $a = new \ArrayIterator($a);
            }
            if (is_array($b)) {
                $b = new \ArrayIterator($b);
            }
            for ($a->rewind(), $b->rewind();
                 $a->valid() && $b->valid();
                 $a->next(), $b->next()) {
                yield self::apply($callable, [$a->current(), $b->current()]);
            }
        };

        return call_user_func_array(self::curry3($_zipWith), $args);
    }

    /**
     * How many arguments the provided callable expects (arity)
     *
     * @param callable $callable
     *
     * @return int
     * @throws \Exception
     */
    private static function _getArity($callable)
    {
        $r = FALSE;
        if (is_array($callable)) {
            $r = new ReflectionMethod($callable[0], $callable[1]);
        } else if (is_string($callable)) {
            if (stripos($callable, '::') !== FALSE) {
                $tmp = explode('::', $callable);
                $r = new ReflectionMethod($tmp[0], $tmp[1]);
            } else {
                $r = new ReflectionFunction($callable);
            }
        } else if (is_a($callable, 'Closure')) {
            $objR = new ReflectionObject($callable);
            $r = $objR->getMethod('__invoke');
        }

        if (!$r) {
            throw new Exception("Could not examine callback");
        }

        return count($r->getParameters());
    }

    /**
     * Determine if the provided array is associative (i.e. not sequential integer keys or non-integer keys)
     *
     * @param array $arr
     *
     * @return bool
     */
    private static function _isAssociativeArray($arr)
    {
        // https://stackoverflow.com/questions/173400/how-to-check-if-php-array-is-associative-or-sequential
        // Credit: Answer by Squirrel
        for (reset($arr); is_int(key($arr)); next($arr)) ;

        return !is_null(key($arr)) ? TRUE : FALSE;
    }

}