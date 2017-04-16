# Pramda

### Practical Functional Programming in PHP
#### P.S. 1: Automatically Curried Functions
#### P.S. 2: Lazy Evaluation baked-in
#### P.S. 3: Fun(ctional) to Use

---

### ELI5: What is Functional Programming and Why Do I Care?

Is it possible that something you're working on could be done in a different way? Turns out that yes it can, and it can be done in multiple ways - or "paradigms" if you prefer a fancier term. One such paradigm is Functional Programming.

Now, if you go and read Haskell's [Functional Programming](https://wiki.haskell.org/Functional_programming) Wiki page, you might get the impression that it's some really complex, weird, unnecessary convoluted ... "thing". That's just because texts about Functional Programming - even introductory ones - are usually written in a dry "academic" way with which most PHP programmers are not accustomed with. In reality though, Functional Programming is about things you already do and espouse in your daily practice, taken to the maximum.

For example, you most probably have been using Composer. The idea behind package managers such as Composer, is that you create stand-alone pieces of work that you can then (at will) compose into something bigger. Well, **congratulations**, that's exactly what Functional Programming is about - but instead of working with "packages", you work with functions. Explained like that, it makes perfect sense. If you understand the benefits of composing packages, you understand the benefits of working with composable functions.

Just as you have to follow some conventions when creating and utilizing a Composer package, you need to follow some conventions when working with functions. The good news is that it's as easy as riding a bicycle. Do you remember how hard it was the first few times? And how it became second nature shortly after? It's the same thing with Functional Programming if you manage to stick with it after the first few falls. At first, things will seem unnatural compared to the way you're used to. Later on, you'll come to realize that just like the bike, Functional Programming can be efficient and fun. And just like you know that using a bicycle is not always the best choice for every situation, you'll know when to pick Functional Programming from your toolbox to sculpt your masterpiece.

#### Show me a code example if you want me to read the wall of text below

Task: 

> From a text file, determine the n most frequently used words, and print out a sorted list of those words along with their frequencies.

Implementation:

```php
$text = P::file('textfile.txt'); // Lazy read line by line with generators

$onlyWords = function ($txt) {
    return preg_split("/[^A-Za-z]/", $txt, NULL, PREG_SPLIT_NO_EMPTY);
};

// Here we go... composing simple functions into what we need
$wordsPerLine = P::map(P::compose($onlyWords, P::unary('strtolower')));
$getFreq = P::countBy('P::identity');
$sortDesc = P::sort('P::negate');
$getWordsFrequencyDesc = P::compose(
    $sortDesc,
    $getFreq,
    'P::flatten',
    $wordsPerLine
);
$topFiveFreq = P::compose('P::toArray', P::take(5), $getWordsFrequencyDesc); // Just the top 5, for fun
$printEm = P::each(function($value, $key) {
   echo $key . ": " . $value . "\n";
});

// And now, we apply to the data
$results = $printEm($topFiveFreq($text));
```

This uses lazy evaluation behind the scenes - which in general only requires a thin boilerplate over an eager implementation (usually `P::map` , `P::flatten` & `P::toArray`). 

On a quick test using _Alice in Wonderland_ (3700 lines) as input, `memory_get_peak_usage` was at **1 MB** vs the nearly **6 MB** of the eager version. Not bad and no need to manually use generators to reap the benefits.

##### Note

That weird `P::unary` that wraps `strtolower`? That's because `P::map` always calls the supplied function with 2 parameters (value, key). Native PHP functions that expect 1 parameter do not ignore the extra parameter, so we need to wrap them inside a closure and pass only 1 parameter to them. `P::unary` takes a function and any number of parameters and passes only the first one to it.

## Understanding Functional Programming for Fun and Profit

We are going to do a down-to-earth roundup of functional programming concepts. Pramda exists so that it makes it easy to utilize all these concepts without the verbosity and boilerplate cost. But you still need to have the understanding of what's going on.

### Composition

If `(gof)(x) = g(f(x))` means something more to you other than ASCII IRC art, you were paying attention to your math teacher at school. You don't need to know about composition in Mathematical terms to do Functional Programming. Just keep in mind that what you'll read below is succinctly expressed by the above relation.

Suppose we have:
````php
function getTweets($username) {
	// returns array of tweets
}
function sortByDateAsc($list) {
	// returns sorted array
}
````

Then we could do:

```php
$myTweets = getTweets('kapolos');
$mySortedTweets = sortByDateAsc($myTweets);
```

Or we could have done:

```php
$mySortedTweets = sortByDateAsc(getTweets('kapolos'));
```

What we wouldn't have done is:

```php
// Function that generates a function that gets and sorts tweets
$sortedTweetsWrapper = function() {
	return function($username) {
		return sortByDateAsc(getTweets($username));
	};
};
$getSortedTweets = $sortedTweetsWrapper();

$mySortedTweets = $getSortedTweets('kapolos');
```

Apart from being an eye-sore, the difference that we care about is that in this snippet `$getSortedTweets` is a function that we created by calling some weird "wrapper" function. `$getSortedTweets` is a function that does the exact same thing as `sortByDateAsc(getTweets($username))` (which is not a function, but a series of "steps").

Ok, so what?

Since it's a function, we can make it more generic:

```php
// Function that generates a function based on two other functions for our example
$compose = function($sort, $fetch) {
	return function($username) use ($sort, $fetch) {
		return $sort($fetch($username));
	};
};
$getSortedTweets = $compose('sortByDateAsc', 'getTweets');

$mySortedTweets = $getSortedTweets('kapolos');
```

It still does the same thing, only now we can pass the fetching and sorting functions as parameters. And all this juggling was done exactly for this reason. To have a function to which we can pass other functions and it will make for us a new function which we can use later on.

In other words, we just described the way in which we are going to be plugging functions together . Doing it this way (creating a function that returns a new function) will allow us to do the cool things later on.

With Pramda:

```php
$getSortedTweets = P::compose('sortByDateAsc', 'getTweets');
$mySortedTweets = $getSortedTweets('kapolos');
```

Notice how the order of execution in composition is from right to left. `P::pipe` can be used instead for left-to-right composition if you prefer it that way.

### Mapping yes, iterations no

Time to forget `for`, `foreach`, `while` and all the other gazillion ways to loop through a list. Instead you will be using `map`, `reduce` and friends.

Whenever you have an array, you will mentally disregard the ability to iterate over its items one by one. Instead, you should be thinking - "_what function can I apply over this set of values to give me a new set that will be like X_"? Yes, the function will still be applying the transformation to each item individually.

Instead of:
```php
$before = [1,2,3,4,5];
$after = [];
foreach ($before as $num) {
	$after[] = $num * 2;
}
```

you will be doing:

```php
$before = [1,2,3,4,5];
$after = P::map(function($num) {
	return $num * 2;
}, $before);
/* P::toArray($after) //=> [2,4,6,8,10] */
```

or 

```php
$before = [1,2,3,4,5];
$doubleNum = function($num) {
	return $num * 2;
};
$doubleAllNumbers = P::map($doubleNum); // Auto-currying ftw (see later on)
$after = $doubleAllNumbers($before);
/* P::toArray($after) //=> [2,4,6,8,10] */
```

So again - why? The answer is (once more) the same. By avoiding direct iteration we will be using functions which, as we saw above, we can compose. Sweet!

Note: Not all iterations can be replaced by `map/reduce` but all can be replaced by recursion. While PHP does not support tail recursion and blows its stack at a depth of 100<sup>1</sup>, we can use the trampoline technique to avoid using the stack for each invocation. Pramda includes a `trampoline` function to help you do that.

<sup>1</sup>: 100 with XDebug enabled, otherwise the exact number depends - the [manual](https://secure.php.net/manual/en/functions.user-defined.php) advises against doing "100-200 recursion levels".

### Currying

Currying is cool. Automatic currying is super cool. But what ... is it?

Consider this simple function:
```php
$add = function ($a, $b) {
	return $a + $b;
};
```

If we want to create a function that increases its argument by 1 using the `add` function above, we would do something like:
```php
$inc = function($value) use ($add){
	return $add($value, 1);
};
/*
 $inc(2); //=> 3
*/
```

If `add` was a curried function, we could have done this:
```php
$inc = $add(1);
/*
 $inc(2); //=> 3
*/
```

In other words, a curried function is able to execute step by step. In the example above, `$add(1)` is a new function that takes one argument. Once it is called, it applies both "1" and the latest passed value to the original `$add`  function.

`P` functions are curried by default (unless it doesn't make sense to, or they are explicitly marked as non-curried).
```php
$inc = P::add(1); // P::add is an existing function
```

You can also manually curry your closures. In our example, to get the curried version of `add` we would have:
```php
$add = function ($a, $b) {
	return $a + $b;
};
$curriedAdd = P::curry2($add);
$inc = $curriedAdd(1);
$inc(2); //=> 3
```

Similarly for a function that takes 3 arguments:

```php
$add3 = function ($a, $b, $c) {
    return $a + $b + $c;
};
$curriedAdd3 = P::curry3($add3);

// One
$add1 = $curriedAdd3(1);
$add1(2,3); //=> 6

// Two
$add1then2 = $curriedAdd3(1, 2);
$add1then2(3); //=> 6

// Two again
$add1then2 = $add1(2);
$add1then2(3); //=> 6
```

Currying combined with composition is what makes Pramda elegant and fun to use.

### Immutable data & no side effects

To avoid any surprises in your code, you need to keep in mind the following guidelines:

* A function should not mutate data passed to it
* A function should not modify variables outside of its scope

In other words, don't pass values by reference and don't use `global`.

Contrived example of big **no-no**:
```php
$counter = 3;
$number = 2;
function toTheEighth(&$number) {
	global $counter;
	
	while ($counter-- > 0) {
		$number *= $number;
	}
	
	return $number;
}

/*
toTheEighth($number) //=> 256
$number //=> 256
$counter //=> 0
*/
```

### Lazy evaluation

When you need to load a small/medium size text file, you probably use `file`, which loads the whole text into an array. When you need to work with a huge file, this approach does not work and you use an alternative implementation with `fgets` to read the content of the file line by line, in order to avoid hitting the memory limit.

Lazy evaluation is about doing the same thing but with objects that implement the `Iterator` interface instead of IO.

Lazy evaluation:

* only use the part of the list you need, when you need it. 
* only provide one item of the list each time another function asks for the list inside a loop

PHP supports lazy evaluation via the `Generator` primitives since v5.5

Eager:
```php
$double = function($arr) {
	$out = [];
	foreach ($arr as $item) {
		$out[] = $item * 2;
	}
	return $out;
}
// $double([1,2,3]); /=> [2,4,6]
```

The eager function will take a copy of the whole array before doing any work on it, then it will compute a new array and return that as the result.

Lazy:
```php
$double = function($arr) {
	foreach($arr as $item) {
		yield $item * 2;
	}
};
$list = $double([1,2,3]);
foreach($list as $item) {
	echo $item.' ';
}
//=> 2 4 6
```

A lazy function takes only one item  from the array each time it needs to and yields (returns) the result as a value of a generator. Whenever (usually at the end of a long path of transformations) we need to use the actual values, we iterate over the generator and get the value of each item.

The great benefit of generators is lower memory usage. Since arrays are not copied on every function, less memory will be occupied.  Given that functional programing is all about passing immutable data to functions, lazy evaluation is a big win.
 

## Pramda

### What's in the name?

I chose to call this library Pramda for 2 reasons:

##### It's sounds like composition of Practical and Lambda
 
"Sounds", because there's that pesky "b" between the "m" and the "d". Prambda just doesn't make the cut. Prabda is meh. Prbda is ... well that's just isn't going to make it either.
Interestingly enough, the Greek word for the letter `λ` is `λάμδα` - no `b` in there.

##### A tribute to Ramda.js

Pramda started as my desire to bring Ramda.js from the world of Javascript over to the PHP lands. Ramda.js has in my humble opinion taken a very balanced approach between practicality and purity/tradition that makes the library fun to use. 

Notable differences include:
* Pramda supports lazy evaluation wherever possible. Efficiency and memory use are major concerns.
* Pramda will not port/follow Fantasy-land spec.
* Not a 1-to-1 port for 2 reasons. First, some things are Javascript-isms that have no place in a PHP library. Furthermore, some functions make better sense for PHP usage if implemented differently from Ramda.js.
* Pramda's target audience has different needs that Ramda.js's audience. So development has to reflect that.

### Usage

You get a `P` class in the global namespace. All functions are static, for example `P::add`.

#### Via Composer

Add this to your `require` section:
`"kapolos/pramda": "0.9.*@dev"`

#### Manually

`require ('src/pramda.php');`

#### Compatibility

Tested on PHP **5.6**. I have avoided using `...args` so it should be working with **5.5** as well but I have not tested that (todo).

#### Tests

Pramda uses the venerable PHPUnit for testing.

```bash

# vendor\bin\phpunit.bat --debug
PHPUnit 4.8.21 by Sebastian Bergmann and contributors.

................................................................ 64 / 79 ( 81%)
...............

Time: 557 ms, Memory: 3.75Mb

OK (79 tests, 244 assertions)

```

### Examples

```php
$planets = [
  [
      "name" => "Earth",
      "order" => 3,
      "has" => ["moon", "oreos"],
      "contact" => [
          "name" => "Bob Spongebob",
          "email" => "bob@spongebob.earth"
      ]
  ],
  [
      "name" => "Mars",
      "order" => 4,
      "has" => ["aliens", "rover"],
      "contact" => [
          "name" => "Marvin Martian",
          "email" => "marvin@the.mars"
      ]
  ],
  [
      "name" => "Venus",
      "order" => 2,
      "has" => ["golden apple"], // https://en.wikipedia.org/wiki/Golden_apple#The_Judgement_of_Paris
      "contact" => [
          "name" => "Aphro Dite",
          "email" => "aphrodite@gods.venus"
      ]
  ],
  [
      "name" => "Mercury",
      "order" => 1,
      "has" => [],
      "contact" => [
          "name" => "Buzz Off",
          "email" => "no-reply@flames.mercury"
      ]
  ],
];
```

> Who are the contacts?

```php
// Functions
$nameOfContact = P::compose(P::prop('name'), P::prop('contact'));
$getContactNames = P::map($nameOfContact);

// Application
$contacts = $getContactNames($planets); // Returns a generator
P::toArray($contacts); //=> ["Bob Spongebob", "Marvin Martian", "Aphro Dite", "Buzz Off"]
```

> Who are the contacts, based on the planet's order from smallest to biggest?

```php
// Functions (cont'd)
$sortByOrderAsc = P::sort(P::prop('order'));  // Returns an array (sort is eager)

// Application
$contacts = $getContactNames($sortByOrderAsc($planets)); // Returns a generator
P::toArray($contacts)); //=> ["Buzz Off", "Aphro Dite", "Bob Spongebob", "Marvin Martian"]
```

> Who are the contacts in reverse alphabetical order?

```php
// Function (cont'd)
$alphaDesc = P::compose('P::negate', 'ord');
$sortByAlphaDesc = P::sort($alphaDesc);

// Application
$contacts = P::apply(P::compose($sortByAlphaDesc, $getContactNames), [$planets]);
// or equivalently
$contacts = P::apply(P::compose($sortByAlphaDesc, $getContactNames), P::of($planets));
// or equivalently
$contacts = $sortByAlphaDesc($getContactNames($planets));

//=> ["Marvin Martian", "Bob Spongebob", "Buzz Off", "Aphro Dite"]
```

> But wait, I meant order by their surnames only, not the full names

```php
// Functions (cont'd)
$lastname = P::compose('P::takeLast', P::split(' '));
$lastnameAlphaDesc = P::compose($alphaDesc, $lastname);
$sortByLastnameAlphaDesc = P::sort($lastnameAlphaDesc);

// Application
$contacts = $sortByLastnameAlphaDesc($getContactNames($planets));
//=> ["Bob Spongebob", "Buzz Off", "Marvin Martian", "Aphro Dite"]
```

> Is Elvis somewhere in the Solar system?

```php
$hasElvis = P::compose(P::contains('Elvis'), P::prop('has'));
P::contains(TRUE, P::map($hasElvis, $planets)); //=> false
```

### Function List

Proper documentation is coming up next. For now, please see the list below and the doc blocks in the source code.
Also, you can see examples how to use each function in the unit tests.

**Legend**:
* `Yes` Explicitly supported
* `No` Explicitly unsupported
* `-` The combination may not make much sense or some other reason. For example, `converge` deals with closures alone and so data evaluation doesn't come into play.
* `Kinda` You should read the associated note.

| Name | Is Lazy | Supports Generators as Input | Curried |
:------------: | :-------------: | :-------------: | :-------------: |
| add | - | - | Yes |
| all | Yes | Yes | Yes |
| allPass | Yes | Yes | Yes |
| add | - | - | Yes |
| append | Yes | Yes | Yes
| appendTo | Yes | Yes | Yes
| apply | No | Yes | Yes
| chain | Yes | Yes | Yes
| compose | No | - | -
| concat | Yes | Yes | Yes
| converge | - | Yes | No
| countBy <sup>1<sup> | Kinda | Yes | Yes
| curry2 | - | - | -
| curry3 | - | - | -
| curryN | - | - | No
| dec | - | - | No
| divide | - | - | Yes
| each | Yes | Yes | Yes
| eq | - | - | Yes
| eqBy | - | - | Yes
| file | Yes | - | -
| filter | Yes | Yes | Yes
| flatten | Yes | Yes | -
| flip <sup>2<sup> | - | - | No
| gt | - | - | Yes
| gte | - | - | Yes
| head | - | Yes | -
| identity | - | Yes | -
| inc | - | - | -
| join | Yes | Yes | Yes
| last | Yes | Yes | -
| lt | - | - | Yes
| lte | - | - | Yes
| map | Yes | Yes | Yes 
| mathMod | - | - | Yes
| max | Yes | Yes | Yes
| maxBy | Yes | Yes | Yes
| merge | No | Yes | Yes
| mergeAll | No | Yes | No
| min | Yes | Yes | Yes
| minBy | Yes | Yes | Yes
| modulo | - | - | Yes
| multiply | - | - | Yes
| nd | - | - | Yes
| negate | - | - | Yes
| nth | Yes | Yes | Yes
| of | - | - | -
| partition | No | Yes | Yes
| pipe | - | Yes | Yes
| pluck | Yes | Yes | Yes
| prepend | Yes | Yes | Yes
| prependTo | Yes | Yes | Yes
| product | Yes | Yes | Yes
| prop | - | No | Yes
| propOf | - | No | Yes
| propOr | - | No | Yes
| props <sup>3<sup> | No | No | Yes
| reduce | - | Yes | Yes
| reverse | No | Yes | -
| set | - | No | Yes
| size | - | Yes | -
| slice | No | Yes | Yes
| sort <sup>4<sup> | No | Yes | Yes
| split | - | - | Yes
| subtract | - | - | Yes
| sum | - | Yes | Yes
| tail | Yes | Yes | -
| take | Yes | Yes | Yes
| takeLast | - | Yes | -
| takeWhile | Yes | Yes | -
| toArray | No | Yes | -
| trampoline | - | - | -
| unapply | - | - | -
| unary | - | - | -
| uniq <sup>4<sup> | No | Yes | -
| values | Yes | Yes | -
| zip | Yes | Yes | Yes
| zipAssoc | Yes | Yes | Yes
| zipWith | Yes | Yes | Yes

#### Notes

1. `countBy` is not lazy in the sense that it return an `array` and not a generator **but** it is lazy in the sense that it will not convert the input into an array first so it will not blow the memory in case of processing huge data as input. But that assumes that the input is such that after processing the result will not be huge.
2. For `flip` to work with a curried function, you _must_ pass its arity as a second parameter, otherwise it won't be able to detect it properly - example:  `$appendTo = P::flip('P::append', 2);`. In general, you should prefer specifying the arity even for non-curried functions because arity detection happens via `Reflection` which is problematic speed-wise.
3. Returns a generator though.
4. It is eager in converting the generator to an array.

### Version Notes

Initial release version is 0.9.0. It will remain < 1.0 until sufficient feedback from usage allows for finalizing of the api. So expect things to change.

### More?

If you are reading this, you are probably interested in investigating the use of Pramda in your work. I will be posting updates, examples and other useful resources on my site. [Add yourself to the notification system](https://app.mailerlite.com/webforms/landing/a6g7r6), it takes less that 9.73 seconds.



