<?php

use PHPUnit\Framework\TestCase;

class Pramda_TestCase extends TestCase
{

    public function testAdd()
    {
        $plusOne = P::add(1);
        $this->assertEquals(3, P::add(2, 1));
        $this->assertEquals(3, $plusOne(2));
    }

    public function testAll()
    {
        $lessThan = P::flip('P::lt', 2);
        $lessThan2 = $lessThan(2);
        $lessThan3 = $lessThan(3);
        $xs = [1, 2];
        $a = function () {
            yield 1;
            yield 2;
        };
        $this->assertFalse(P::all($lessThan2, $xs));
        $this->assertTrue(P::all($lessThan3, $xs));
        $this->assertFalse(P::all($lessThan2, $a()));
        $this->assertTrue(P::all($lessThan3, $a()));
    }

    public function testAllPass()
    {
        $isMultipleOf = P::curry2(function ($base, $value) {
            $modBy = P::flip('P::mathMod', 2);
            $modByX = $modBy($base);

            return $modByX($value) > 0 ? FALSE : TRUE;
        });
        $isEven = $isMultipleOf(2);
        $isMultipleOf10 = $isMultipleOf(10);
        $isEvenMultipleOfTen = P::allPass([$isEven, $isMultipleOf10]);

        $this->assertTrue(P::allPass([$isEven, $isMultipleOf10], 20));
        $this->assertFalse(P::allPass([$isEven, $isMultipleOf10], 22));
        $this->assertFalse(P::allPass([$isEven, $isMultipleOf10], 21));
        $this->assertTrue($isEvenMultipleOfTen(20));
        $this->assertFalse($isEvenMultipleOfTen(22));
        $a = function () use ($isEven, $isMultipleOf) {
            yield $isEven;
            yield $isMultipleOf;
        };
        $this->assertTrue(P::allPass([$isEven, $isMultipleOf10], 20));
    }

    public function testAny()
    {
        $lessThan = P::flip('P::lt', 2);
        $lessThan2 = $lessThan(2);
        $lessThan3 = $lessThan(3);
        $xs = [2, 4];
        $this->assertFalse(P::any($lessThan2, $xs));
        $this->assertTrue(P::any($lessThan3, $xs));
        $a = function () {
            yield 2;
            yield 4;
        };
        $this->assertFalse(P::any($lessThan2, $a()));
        $this->assertTrue(P::any($lessThan3, $a()));
    }

    public function testAppend()
    {
        $p1 = P::append(3);
        $this->assertEquals([1, 2, 3], P::toArray(P::append(3, [1, 2])));
        $this->assertEquals([1, 2, [3]], P::toArray(P::append([3], [1, 2])));
        $this->assertEquals([1, 2, 3], P::toArray($p1([1, 2])));
        $this->assertEquals([1, 2, 3, '4'], P::toArray(P::append('4', [1, 2, 3])));
        $this->assertEquals([3, '4', [1, 2]], P::toArray(P::append([1, 2], [3, '4'])));
        $a = function () {
            yield 1;
            yield 2;
            yield 3;
        };
        $res = P::toArray(P::append('4', $a()));
        $this->assertEquals([1, 2, 3, '4'], $res);
        $this->assertEquals(['a' => 'b', 3, '4'], P::toArray(P::append('4', ['a' => 'b', 3])));
    }

    public function testAppendTo()
    {
        $p1 = P::appendTo([1, 2]);
        $this->assertEquals([1, 2, 3], P::toArray(P::appendTo([1, 2], 3)));
        $this->assertEquals([1, 2, [3]], P::toArray(P::appendTo([1, 2], [3])));
        $this->assertEquals([1, 2, 3], P::toArray($p1(3)));
        $this->assertEquals([1, 2, 3, '4'], P::toArray(P::appendTo([1, 2, 3], '4')));
        $this->assertEquals([3, '4', [1, 2]], P::toArray(P::appendTo([3, '4'], [1, 2])));
        $a = function () {
            yield 1;
            yield 2;
            yield 3;
        };
        $this->assertEquals([1, 2, 3, '4'], P::toArray(P::appendTo($a(), '4')));
        $appendTo = P::flip('P::append', 2);
        $this->assertEquals([1, 2, 3], P::toArray($appendTo([1, 2], 3)));
    }

    public function testApply()
    {
        $maxPos = function () {
            $max = 0;
            foreach (func_get_args() as $num) {
                if ($num > $max) {
                    $max = $num;
                }
            }

            return $max;
        };
        $max = P::apply($maxPos);
        $a = function () {
            yield 1;
            yield 3;
            yield 5;
            yield 2;
            yield 2;
        };
        $this->assertEquals(5, $max($a()));
    }

    public function testChain()
    {
        $duplicate = function ($n) {
            return [$n, $n];
        };
        $this->assertEquals([1, 1, 2, 2, 3, 3], P::toArray(P::chain($duplicate, [1, 2, 3])));
        $dup = P::chain($duplicate);
        $a = function () {
            yield 1;
            yield 2;
            yield 3;
        };
        $this->assertEquals([1, 1, 2, 2, 3, 3], P::toArray($dup($a())));
        $b = [[1, 2], [3, 4], [5, 6]];
        $this->assertEquals([1, 2, 3, 4, 5, 6], P::toArray(P::chain('P::identity', $b)));
    }

    public function testCompose()
    {
        $triple = function ($x) {
            return $x * 3;
        };
        $double = function ($x) {
            return $x * 2;
        };
        $square = function ($x) {
            return $x * $x;
        };
        $combo = P::compose($triple, $double, $square);
        $this->assertEquals(150, $combo(5));
    }

    public function testConcat()
    {
        $a = [1, 2, 3];
        $b = [1, 2, 4];
        $c = P::concat($a);
        $this->assertEquals([1, 2, 3, 1, 2, 4], P::toArray(P::concat($a, $b)));
        $this->assertEquals([1, 2, 3, 1, 2, 4], P::toArray($c($b)));
        $this->assertEquals([1, 2], P::toArray(P::concat([1, 2], [])));
        $a = function () {
            yield 1;
            yield 2;
        };
        $b = function () {
            yield 1;
            yield 'b' => 'c';
        };
        $this->assertEquals([1, 2, 1], P::toArray(P::concat($a(), [1])));
        $this->assertEquals([1, 'b' => 'c', 1], P::toArray(P::concat($b(), [1])));
        $this->assertEquals([1, 'b' => 'c'], P::toArray(P::concat($b(), ['b' => 'c'])));
    }

    public function testContains()
    {
        $this->assertTrue(P::contains(3, [1, 2, 3]));
        $a = function () {
            yield 'a' => 1;
            yield 'b' => [42];
        };
        $this->assertTrue(P::contains([42], $a()));
    }

    public function testConverge()
    {
        $add = function ($a, $b) {
            return $a + $b;
        };
        $multiply = function ($a, $b) {
            return $a * $b;
        };
        $subtract = function ($a, $b) {
            return $a - $b;
        };

        $conv1 = P::converge($multiply, [$add, $subtract]);
        $this->assertEquals(-3, $conv1(1, 2));

        $add3 = function ($a, $b, $c) {
            return $a + $b + $c;
        };

        $conv2 = P::converge($add3, [$multiply, $add, $subtract]);
        $this->assertEquals(4, $conv2(1, 2));

        $a = function () use ($add, $subtract) {
            yield $add;
            yield $subtract;
        };
        $conv1 = P::converge($multiply, $a());
        $this->assertEquals(-3, $conv1(1, 2));
    }

    public function testCountBy()
    {
        $numbers = [1.0, 1.1, 1.2, 2.0, 3.0, 2.2];
        $countByFloor = P::countBy(P::compose('intval', 'floor'));
        $this->assertEquals(['1' => 3, '2' => 2, '3' => 1], $countByFloor($numbers));
        $a = function () {
            yield 1.0;
            yield 1.1;
            yield 2.2;
            yield 2.4;
        };
        $this->assertEquals(['1' => 2, '2' => 2], $countByFloor($a()));
    }

    public function testCurry2()
    {
        $sum2 = function ($a, $b) {
            return $a + $b;
        };
        $curriedSum2 = P::curry2($sum2);
        $addToFive = $curriedSum2(5);
        $this->assertEquals(8, $addToFive(3));
        $this->assertEquals(8, $curriedSum2(3, 5));
    }

    public function testCurry3()
    {
        $sum3 = function ($a, $b, $c) {
            return $a + $b + $c;
        };
        $curriedSum3 = P::curry3($sum3);
        $plus5 = $curriedSum3(5);
        $plus10 = $plus5(10);
        $minus4 = $plus10(-4);
        $this->assertEquals(11, $minus4);
        $this->assertEquals(11, $plus5(10, -4));
    }

    public function testCurryN()
    {
        $sum2 = function ($a, $b) {
            return $a + $b;
        };
        $curriedSum2 = P::curryN(2, $sum2);
        $addToFive = $curriedSum2(5);
        $this->assertEquals(8, $addToFive(3));
        $this->assertEquals(8, $curriedSum2(3, 5));

        $sum3 = function ($a, $b, $c) {
            return $a + $b + $c;
        };
        $curriedSum3 = P::curryN(3, $sum3);
        $plus5 = $curriedSum3(5);
        $plus10 = $plus5(10);
        $minus4 = $plus10(-4);
        $this->assertEquals(11, $minus4);
        $this->assertEquals(11, $plus5(10, -4));
    }

    public function testDec()
    {
        $this->assertEquals(2, P::dec(3, 1));
    }

    public function testDivide()
    {
        $divideWith = P::flip('P::divide', 2);
        $perCent = $divideWith(100);
        $reciprocal = P::divide(1);
        $this->assertEquals(9000, P::divide(900000, 100));
        $this->assertEquals(9000, $perCent(900000));
        $this->assertEquals(1 / 10, $reciprocal(10));
    }

    public function testEach()
    {
        $assertModuloEqualsOne = function ($number) {
            $this->assertEquals(1, $number % 2);
        };
        P::each($assertModuloEqualsOne, [1, 3]);

        $assertModuloEqualsOne = function ($number, $index) {
            $this->assertEquals(1, $number % 2);
        };
        $a = function () {
            yield 'a' => 1;
            yield 'b' => 3;
        };
        P::each($assertModuloEqualsOne, $a());
    }

    public function testEq()
    {
        $this->assertEquals(TRUE, P::eq(1, 1));
        $this->assertEquals(FALSE, P::eq(1, 2));
    }

    public function testEqBy()
    {
        $add1 = P::add(1);
        $this->assertEquals(TRUE, P::eqBy($add1, 1, 1));
        $this->assertEquals(FALSE, P::eqBy($add1, 1, 2));
    }

    public function testFilter()
    {
        $list1 = function () {
            yield json_decode('{"a": 1, "b":1}');
            yield json_decode('{"a": 2, "b":2}');
            yield json_decode('{"a": 3, "b":3}');
        };
        $list2 = [
            json_decode('{"a": 1, "b":1}'),
            json_decode('{"a": 2, "b":2}'),
            json_decode('{"a": 3, "b":3}')
        ];
        $aIsTwo = function ($item) {
            return $item->a == 2 ? TRUE : FALSE;
        };
        $this->assertEquals([json_decode('{"a": 2, "b":2}')], P::toArray(P::filter($aIsTwo, $list1())));
        $this->assertEquals([json_decode('{"a": 2, "b":2}')], P::toArray(P::filter($aIsTwo, $list2)));

        $list3 = [1, 2];
        $valueIsOne = function ($v) {
            return $v === 1 ? TRUE : FALSE;
        };
        $this->assertEquals(1, P::head(P::filter($valueIsOne, $list3)));

        $list4 = ['a' => 1, 'b' => 2];
        $valueIsOne = function ($v, $key) {
            return $v === 1 ? TRUE : FALSE;
        };
        $this->assertEquals(['a' => 1], P::head(P::filter($valueIsOne, $list4)));

    }

    public function testFlatten()
    {
        $nestedList = [1, 2, [3, 4], 5, [6, [7, 8, [9, [10, 11], 12]]]];
        $flattened = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
        $this->assertEquals($flattened, P::toArray(P::flatten($nestedList)));
        $nestedList = [1, [2], [3, [4, 5], 6, [[[7], 8]]], 9, 10];
        $flattened = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
        $this->assertEquals($flattened, P::toArray(P::flatten($nestedList)));
        $nestedList = [1, [2], [3, [4, 5], 6, [[[7], 8]]], 9, 10];
        $flattened = [1, [2], [3, [4, 5], 6, [[[7], 8]]], 9, 10];
        $this->assertNotEquals($flattened, P::toArray(P::flatten($nestedList)));
        $nestedList = [[], [], []];
        $flattened = [];
        $this->assertEquals($flattened, P::toArray(P::flatten($nestedList)));
        $assoc = [
            'a' => 'b',
            'c' => ['d' => 'e'],
            'f' => ['g' => 'h',
                    'i' => ['j' => 'k']
            ],
            'l' => [
                'm' => [
                    'n' => [
                        'o' => ['p' => 'q']
                    ]
                ]
            ]
        ];
        $a = function () {
            yield [1, [2, 3]];
            yield 4;
            yield [5];
        };
        $this->assertEquals([1, 2, 3, 4, 5], P::toArray(P::flatten($a())));
    }

    public function testFlip()
    {
        $f = function ($a, $b) {
            return $a . $b;
        };
        $g = P::flip($f);
        $this->assertEquals('ba', $g('a', 'b'));
        $f = function ($a, $b, $c) {
            return $a . $b . $c;
        };
        $g = P::flip($f);
        $this->assertEquals('bac', $g('a', 'b', 'c'));
    }

    public function testGt()
    {
        $lessThan2 = P::gt(2);
        $greaterThan = P::flip('P::gt', 2);
        $greaterThan2 = $greaterThan(2);
        $this->assertFalse(P::gt(2, 4));
        $this->assertTrue(P::gt(2, 0));
        $this->assertTrue($lessThan2(1));
        $this->assertTrue($greaterThan2(3));
    }

    public function testGte()
    {
        $upTo2 = P::gte(2);
        $atleast = P::flip('P::gte', 2);
        $atleast2 = $atleast(2);
        $this->assertFalse(P::gte(2, 4));
        $this->assertTrue(P::gte(2, 0));
        $this->assertFalse($upTo2(3));
        $this->assertTrue($upTo2(2));
        $this->assertFalse($atleast2(1));
        $this->assertTrue($atleast2(2));
    }

    public function testHead()
    {
        $this->assertEquals(1, P::head([1, 2, 3]));
        $this->assertEquals(NULL, P::head([]));
    }

    public function testIdentity()
    {
        $this->assertEquals('1', P::identity('1'));
        $a = function () {
            yield '1';
        };
        $this->assertEquals($a(), P::identity($a()));
    }

    public function testInc()
    {
        $this->assertEquals(4, P::inc(3));
    }

    public function testJoin()
    {
        $a = function () {
            yield 'The';
            yield 'Rain In';
            yield 'Spain';
        };
        $this->assertEquals('The Rain In Spain', P::join(' ', ['The', 'Rain In', 'Spain']));
        $this->assertEquals('The Rain In Spain', P::join(' ', $a()));
    }

    public function testLast()
    {
        $a = function () {
            yield '1';
            yield '2';
            yield '3';
        };
        $this->assertEquals(3, P::last([1, 2, 3]));
        $this->assertEquals(NULL, P::last([]));
        $this->assertEquals(3, P::last($a()));
        $b = ['a' => 1, 'b' => 2];
        $this->assertEquals(['b' => 2], P::last($b));
    }

    public function testLt()
    {
        $greaterThan2 = P::lt(2);
        $lessThan = P::flip('P::lt', 2);
        $lessThan2 = $lessThan(2);
        $this->assertFalse(P::lt(4, 2));
        $this->assertTrue(P::lt(0, 2));
        $this->assertTrue($lessThan2(1));
        $this->assertTrue($greaterThan2(3));
    }

    public function testLte()
    {
        $atleast2 = P::lte(2);
        $upTo = P::flip('P::lte', 2);
        $upTo2 = $upTo(2);
        $this->assertFalse(P::lte(4, 2));
        $this->assertTrue(P::lte(2, 2));
        $this->assertTrue(P::lte(0, 2));
        $this->assertFalse($upTo2(3));
        $this->assertTrue($upTo2(2));
        $this->assertTrue($upTo2(1));
        $this->assertTrue($atleast2(3));
        $this->assertTrue($atleast2(2));
        $this->assertFalse($atleast2(1));
    }

    public function testMap()
    {
        $a = function () {
            yield 1;
            yield 3;
        };
        $modulo = function ($number) {
            return $number % 2;
        };
        $modded = P::toArray(P::map($modulo, $a()));
        $this->assertEquals(2, $modded[0] + $modded[1]);

        $biasedModulo = function ($number, $index) {
            return $number % 2 + (int)$index;
        };
        $a = function () {
            yield '4' => 1;
            yield '5' => 2;
        };
        $modded = P::toArray(P::map($biasedModulo, $a()));
        $this->assertEquals(10, $modded[0] + $modded[1]);
    }

    public function testMathMod()
    {
        $modBase = P::flip('P::mathMod', 2);
        $clock = $modBase(12);

        $this->assertEquals(1, P::mathMod(7, 3));
        $this->assertEquals(2, P::mathMod(-7, 3));
        $this->assertEquals(9, $clock(21));
    }

    public function testMax()
    {
        $a = function () {
            yield 1;
            yield 3;
            yield 5;
            yield 2;
            yield 4;
        };
        $this->assertEquals(5, P::max($a()));
    }

    public function testMaxBy()
    {
        $comp = function ($a, $b) {
            return ord($a) > ord($b) ? TRUE : FALSE;
        };
        $maxByLetter = P::maxBy($comp);

        $a = function () {
            yield 'c';
            yield 'unix';
            yield 'bbb';
            yield 'aaa';
        };
        $this->assertEquals('unix', P::maxBy($comp, ['c', 'unix', 'bbb', 'aaa']));
        $this->assertEquals('unix', $maxByLetter($a()));
    }

    public function testMerge()
    {
        $a = ["color" => "red", 2, 4];
        $b = ["a", "b", "color" => "green", "shape" => "trapezoid", 4];
        $mergeWithA = P::merge($a);
        $this->assertEquals(["color" => "green", 2, 4, "a", "b", "shape" => "trapezoid", 4], P::merge($a, $b));
        $this->assertEquals(["color" => "green", 2, 4, "a", "b", "shape" => "trapezoid", 4], $mergeWithA($b));
        $this->assertEquals([1, 2], P::merge([1, 2], []));
        $a = function () {
            yield 1;
            yield 2;
        };
        $this->assertEquals([1, 2, 1], (P::merge($a(), [1])));
    }

    public function testMergeAll()
    {
        $a = ["color" => "red", 2, 4];
        $b = ["a", "b", "color" => "green", "shape" => "trapezoid", 4];
        $c = ["shape" => "triangle"];
        $this->assertEquals(["color" => "green", 2, 4, "a", "b", "shape" => "triangle", 4], P::mergeAll([$a, $b, $c]));
        $this->assertEquals([1, 2], P::mergeAll([[1, 2], []]));
        $a = function () {
            yield 1;
            yield 2;
        };
        $this->assertEquals([1, 2, 1], (P::mergeAll([$a(), [1]])));
    }

    public function testMin()
    {
        $a = function () {
            yield 4;
            yield 3;
            yield 5;
            yield 1;
            yield 2;
        };
        $this->assertEquals(1, P::min([4, 3, 5, 1, 2]));
        $this->assertEquals(1, P::min($a()));
    }

    public function testMinBy()
    {
        $comp = function ($a, $b) {
            return ord($a) > ord($b) ? TRUE : FALSE;
        };
        $minByLetter = P::minBy($comp);

        $a = function () {
            yield 'c';
            yield 'unix';
            yield 'bbb';
            yield 'aaa';
        };
        $this->assertEquals('aaa', P::minBy($comp, ['c', 'unix', 'bbb', 'aaa']));
        $this->assertEquals('aaa', $minByLetter(['c', 'unix', 'bbb', 'aaa']));
        $this->assertEquals('aaa', $minByLetter($a()));
    }

    public function testModulo()
    {
        $modBy = P::flip('P::modulo', 2);
        $isOdd = $modBy(2);

        $this->assertFalse((bool)$isOdd(9000));
        $this->assertTrue((bool)$isOdd(11));
        $this->assertEquals(1, P::modulo(7, 2));
    }

    public function testMultiply()
    {
        $triple = P::multiply(3);

        $this->assertEquals(15, $triple(5));
    }

    public function testNd()
    {
        $oneIsTrue = P::nd(TRUE);
        $otherIsFalse = P::nd(FALSE);

        $this->assertTrue($oneIsTrue(TRUE));
        $this->assertFalse($oneIsTrue(FALSE));
        $this->assertFalse($otherIsFalse(FALSE));
        $this->assertFalse($otherIsFalse(TRUE));
        $this->assertTrue($oneIsTrue(1));
        $this->assertFalse($oneIsTrue(''));
    }

    public function testNegate()
    {
        $this->assertEquals(-5, P::negate(5));
        $this->assertEquals(5, P::negate(-5));
    }

    public function testNth()
    {
        $this->assertEquals(1, P::nth(0, [1, 2, 3]));

        $this->assertEquals(2, P::nth(1, [1, 2, 3]));
        $this->assertEquals(NULL, P::nth(2, []));
        $this->assertEquals(3, P::nth(2, ["a" => 1, "b" => 2, "c" => 3]));
    }

    public function testOf()
    {
        $this->assertEquals([5], P::of(5));
    }

    public function testPartition()
    {
        $a = function () {
            yield 5;
            yield 8;
            yield 12;
            yield 10;
        };
        $is5x = function ($num) {
            return $num % 5 === 0 ? TRUE : FALSE;
        };
        $only5x = P::partition($is5x);
        $this->assertEquals([[5, 10], [8, 12]], $only5x($a()));
    }

    public function testPipe()
    {
        $triple = function ($x) {
            return $x * 3;
        };
        $double = function ($x) {
            return $x * 2;
        };
        $square = function ($x) {
            return $x * $x;
        };
        $pipe = P::pipe($square, $double, $triple);
        $this->assertEquals(150, $pipe(5));
    }

    public function testPluck()
    {
        $a = function () {
            yield ['a' => 1, 'b' => 2];
            yield ['a' => 3, 'b' => 4];
        };
        $b1 = [1, 2, 3];
        $b2 = [4, 5, 6];
        $this->assertEquals([2, 4], P::toArray(P::pluck('b', $a())));
        $this->assertEquals([2, 5], P::toArray(P::pluck(1, [$b1, $b2])));
    }

    public function testPrepend()
    {
        $p1 = P::prepend(1);
        $this->assertEquals([1, 2, 3], P::toArray(P::prepend(1, [2, 3])));
        $this->assertEquals([[1], 2, 3], P::toArray(P::prepend([1], [2, 3])));
        $this->assertEquals([1, 2, 3], P::toArray($p1([2, 3])));
        $a = function () {
            yield 2;
            yield 3;
        };
        $this->assertEquals([1, 2, 3], P::toArray(P::prepend(1, $a())));
    }

    public function testPrependTo()
    {
        $p1 = P::prependTo([2, 3]);
        $this->assertEquals([1, 2, 3], P::toArray(P::prependTo([2, 3], 1)));
        $this->assertEquals([[1], 2, 3], P::toArray(P::prependTo([2, 3], [1])));
        $this->assertEquals([1, 2, 3], P::toArray($p1(1)));
        $a = function () {
            yield 2;
            yield 3;
        };
        $this->assertEquals([1, 2, 3], P::toArray(P::prependTo($a(), 1)));
    }

    public function testProduct()
    {
        $a = function () {
            yield 1;
            yield 2;
            yield 3;
            yield 4;
            yield 5;
        };
        $this->assertEquals(120, P::product($a()));
    }

    public function testProp()
    {
        $obj = new \StdClass();
        $obj->x = 100;
        $assoc = ["x" => 100];
        $indexed = [100, 200];
        $this->assertEquals(100, P::prop('x', $obj));
        $this->assertEquals(NULL, P::prop('y', $obj));
        $this->assertEquals(100, P::prop('x', $assoc));
        $this->assertEquals(NULL, P::prop('y', $assoc));
        $this->assertEquals(200, P::prop('1', $indexed));
        $this->assertEquals(200, P::prop(1, $indexed));
        $this->assertEquals(NULL, P::prop('3', $indexed));
    }

    public function testPropOf()
    {
        $assoc = ['x' => 100, 'y' => 200, 'z' => 300];
        $this->assertEquals(P::prop('x', $assoc), P::propOf($assoc, 'x'));
    }

    public function testPropOr()
    {
        $assoc = ["x" => 100];
        $x = P::propOr('x');
        $y = P::propOr('y');
        $xOr200 = $x(200);
        $yOr200 = $y(200);
        $this->assertEquals(100, P::propOr('x', 200, $assoc));
        $this->assertEquals(200, P::propOr('y', 200, $assoc));
        $this->assertEquals(100, $xOr200($assoc));
        $this->assertEquals(200, $yOr200($assoc));
    }

    public function testProps()
    {
        $assoc = ['x' => 100, 'y' => 200, 'z' => 300];
        $this->assertEquals([200, 300], P::toArray(P::props(['y', 'z'], $assoc)));
        $this->assertEquals([200, NULL, 300], P::toArray(P::props(['y', 'a', 'z'], $assoc)));
    }

    public function testReduce()
    {
        $a = function () {
            yield 1;
            yield 2;
            yield 3;
        };
        $add = function ($a, $b) {
            return $a + $b;
        };
        $this->assertEquals(16, P::reduce($add, 10, $a()));
        $reduceByAdding = P::reduce($add, 0);
        $this->assertEquals(6, $reduceByAdding($a()));

        $b = ['a' => 1, 'b' => 2];
        $concatKeyVals = function ($acc, $value, $key) {
            return $acc . (string)$key . (string)$value;
        };
        $this->assertEquals('a1b2', P::reduce($concatKeyVals, '', $b));
    }

    public function testReverse()
    {
        $a = function () {
            yield 1;
            yield 2;
            yield 3;
        };
        $this->assertTrue([3, 2, 1] === P::reverse($a()));
        $this->assertTrue(['2', '1'] === P::reverse(['1', '2']));
        $this->assertTrue([1] === P::reverse([1]));
        $this->assertTrue([] === P::reverse([]));
        $this->assertTrue(['c' => '3', 'b' => '2', 'a' => '1'] === P::reverse(['a' => '1', 'b' => '2', 'c' => '3']));
    }

    public function testSet()
    {
        $now = time();
        $noob = ['lvl' => 1, 'created' => $now];
        $lvl9000 = P::set('lvl', 9000);
        $this->assertEquals(['lvl' => 9000, 'created' => $now], $lvl9000($noob));
    }

    public function testSize()
    {
        $a = function () {
            yield 1;
            yield 2;
            yield 3;
        };
        $this->assertEquals(3, P::size($a()));
        $this->assertEquals(3, P::size([1, 2, 3]));
        $o = new StdClass();
        $o->a = 1;
        $o->b = 2;
        $o->c = 3;
        $this->assertEquals(3, P::size($o));
    }

    public function testSlice()
    {
        $this->assertEquals(['b', 'c'], P::slice(1, 3, ['a', 'b', 'c', 'd']));
        $this->assertEquals(['b', 'c', 'd'], P::slice(1, NULL, ['a', 'b', 'c', 'd']));
        $this->assertEquals(['a', 'b', 'c'], P::slice(0, -1, ['a', 'b', 'c', 'd']));
        $this->assertEquals(['b', 'c'], P::slice(-3, -1, ['a', 'b', 'c', 'd']));
        $this->assertEquals(['b', 'c'], P::slice(-3, -1, ['a', 'b', 'c', 'd']));
        $this->assertEquals(['c' => 'd', 'e' => 'f'], P::slice(-3, -1, ['a' => 'b', 'c' => 'd', 'e' => 'f', 'g' => 'h']));
        $a = function () {
            yield 'a';
            yield 'b';
            yield 'c';
            yield 'd';
        };
        $this->assertEquals(['b', 'c'], P::slice(1, 3, $a()));
    }

    public function testSort()
    {
        $this->assertTrue([2, 4, 5, 7] === P::sort('P::identity', [4, 2, 7, 5]));
        $this->assertTrue([2, 4, 5, 7] === P::sort('P::identity', [4, 2, 7, 5]));
        $this->assertTrue([3.9, 4, 5, 7] === P::sort('P::identity', [4, 3.9, 7, 5]));

        $a = function () {
            yield 4;
            yield 2;
            yield 7;
            yield 5;
        };
        $this->assertTrue([2, 4, 5, 7] === P::sort('P::identity', $a()));

        $assoc = ['a' => 'C', 'b' => 'D', 'c' => 'A'];
        $this->assertTrue(['c' => 'A', 'a' => 'C', 'b' => 'D'] === P::sort('ord', $assoc));
    }

    public function testSplit()
    {
        $this->assertEquals(['a', 'b', 'c', 'xyz', 'd'], P::split('.', 'a.b.c.xyz.d'));
    }

    public function testSubtract()
    {
        $minus = P::flip('P::subtract', 2);
        $minus7 = $minus(7);

        $this->assertEquals(1, P::subtract(8, 7));
        $this->assertEquals(1, $minus7(8));
    }

    public function testSum()
    {
        $this->assertEquals(15, P::sum([1, 2, 3, 4, 5]));
        $a = function () {
            yield 1;
            yield 2;
            yield 3;
            yield 4;
            yield 5;
        };
        $this->assertEquals(15, P::sum($a()));
    }

    public function testTail()
    {
        $this->assertEquals([2, 3], P::toArray(P::tail([1, 2, 3])));
        $a = function () {
            yield 1;
            yield 2;
            yield 3;
        };
        $this->assertEquals([2, 3], P::toArray(P::tail($a())));
        $this->assertEquals([], P::toArray(P::tail([])));
        $stack = ["orange", "banana", "apple", "raspberry"];
        $this->assertEquals(["banana", "apple", "raspberry"], P::toArray(P::tail($stack)));
        $stack = ['a' => '1', 'b' => 2, 'c' => 3];
        $this->assertEquals(['b' => 2, 'c' => 3], P::toArray(P::tail($stack)));
    }

    public function testTake()
    {
        $list = [1, 2, 3, 4, 5, 6, 7, 8, 9];
        $this->assertEquals([1, 2, 3, 4, 5], P::toArray(P::take(5, $list)));

        $a = function () {
            for ($i = 1; $i < 10; $i++) {
                yield $i;
            }
        };
        $this->assertEquals([1, 2, 3, 4, 5], P::toArray(P::take(5, $a())));

        $listAssoc = ['a' => 1, 'b' => '2', 'c' => 3];
        $this->assertEquals(['a' => 1, 'b' => '2'], P::toArray(P::take(2, $listAssoc)));
    }

    public function testTakeLast()
    {
        $list = [1, 2, 3, 4, 5, 6, 7, 8, 9];
        $this->assertEquals(9, P::takeLast($list));
        $listAssoc = ['a' => 1, 'b' => '2', 'c' => 3];
        $this->assertEquals(['c' => 3], P::takeLast($listAssoc));
    }

    public function testTakeWhile()
    {
        $list = [1, 2, 3, 4, 5, 6, 7, 8, 9];
        $lessThanFive = function ($value) {
            return $value < 5 ? TRUE : FALSE;
        };
        $this->assertEquals([1, 2, 3, 4], P::toArray(P::takeWhile($lessThanFive, $list)));
        $listAssoc = ['a' => 1, 'b' => '2', 'c' => 7];
        $this->assertEquals(['a' => 1, 'b' => '2'], P::toArray(P::takeWhile($lessThanFive, $listAssoc)));
        $keyNotC = function ($value, $key) {
            return $key === 'c' ? FALSE : TRUE;
        };
        $this->assertEquals(['a' => 1, 'b' => '2'], P::toArray(P::takeWhile($keyNotC, $listAssoc)));
    }

    public function testToArray()

    {
        $list = [1, 2, 3, 4, 5];
        $this->assertEquals([1, 2, 3, 4, 5], P::toArray($list));

        $a = function () {
            for ($i = 1; $i < 6; $i++) {
                yield $i;
            }
        };
        $this->assertEquals([1, 2, 3, 4, 5], P::toArray($a()));

        $listAssoc = ['a' => 1, 'b' => '2'];
        $this->assertEquals(['a' => 1, 'b' => '2'], P::toArray($listAssoc));
        $b = function () {
            yield 'a' => 1;
            yield 'b' => 2;
        };
        $this->assertEquals(['a' => 1, 'b' => '2'], P::toArray($b()));
    }

    public function testUnapply()
    {
        $f = P::unapply('json_encode');
        $this->assertEquals('[1,"3",2,2,{"a":"b"}]', $f(1, "3", 1 + 1, 2, ["a" => "b"]));
    }

    public function testUnary()
    {
        $strl = P::unary('strtolower');
        $this->assertEquals('aaa', $strl('AAA'));
    }

    public function testUniq()
    {
        $this->assertEquals([1, 2, 3, 4], P::uniq([1, 1, 2, 2, 3, 4, 3, 4, 1]));
        $a = function () {
            yield 1;
            yield 2;
            yield 3;
            yield 4;
        };
        $this->assertEquals([1, 2, 3, 4], P::uniq($a()));
    }

    public function testUnnest()
    {
        $a = function () {
            yield 1;
            yield [2];
            yield [[3]];
        };
        $a = [1, 2, [[3]]];
        $this->assertEquals([1, 2, [3]], P::toArray(P::unnest($a)));
        $b = [[1, 2], [3, 4], [5, 6]];
        $this->assertEquals([1, 2, 3, 4, 5, 6], P::toArray(P::unnest($b)));
    }

    public function testValues()
    {
        $listAssoc = ['a' => 1, 'b' => '2', 'c' => 3];
        $this->assertEquals([1, 2, 3], P::toArray(P::values($listAssoc)));
    }

    public function testZip()
    {
        $z1 = P::compose(['P', 'toArray'], P::zip([1, 2]));
        $this->assertEquals([[1, 'a'], [2, 'b']], P::toArray(P::zip([1, 2], ['a', 'b'])));
        $this->assertEquals([[1, 'a'], [2, 'b']], $z1(['a', 'b']));
        $this->assertEquals([], P::toArray(P::zip([], [2, 'b'])));
        $a = function () {
            yield 1;
            yield 2;
        };
        $b = function () {
            yield 'a';
            yield 'b';
        };
        $this->assertEquals([[1, 'a'], [2, 'b']], P::toArray(P::zip([1, 2], ['a', 'b'])));
        $a = ['a' => 1, 'b' => 2];
        $b = ['c' => 3, 'd' => 4];
        $this->assertEquals([[1, 3], [2, 4]], P::toArray(P::zip($a, $b)));
    }

    public function testZipAssoc()
    {
        $this->assertEquals(['a' => 1, 'b' => 2], P::toArray(P::zipAssoc(['a', 'b'], [1, 2])));
        $z1 = P::zipAssoc(['a', 'b']);
        $this->assertEquals(['a' => 1, 'b' => 2], P::toArray($z1([1, 2])));
        $this->assertEquals([], P::toArray(P::zipAssoc([], [1, 2])));
        $a = function () {
            yield 'a';
            yield 'b';
        };
        $b = function () {
            yield 1;
            yield 2;
        };
        $this->assertEquals(['a' => 1, 'b' => 2], P::toArray(P::zipAssoc($a(), $b())));
    }

    public function testZipWith()
    {
        $f = function ($a, $b) {
            return $a . $b;
        };
        $concat = P::compose(['P', 'toArray'], P::zipWith($f));
        $concatOneTwo = P::zipWith($f, [1, 2]);
        $this->assertEquals(['1a', '2b'], P::toArray(P::zipWith($f, [1, 2], ['a', 'b'])));
        $this->assertEquals(['1a', '2b'], $concat([1, 2], ['a', 'b']));
        $this->assertEquals(['1a', '2b'], P::toArray($concatOneTwo(['a', 'b'])));

        $a = function () {
            yield 'a';
            yield 'b';
        };
        $this->assertEquals(['1a', '2b'], P::toArray(P::zipWith($f, [1, 2], $a())));
        $this->assertEquals(['1a', '2b'], $concat([1, 2], $a()));
    }
}
