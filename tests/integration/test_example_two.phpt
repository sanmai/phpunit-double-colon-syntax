--TEST--
PHPUnit finds two tests
--FILE--
<?php
passthru('./vendor/bin/phpunit --order-by=default --colors=never tests/ExampleTest.php::test_one tests/ExampleTest.php::test_two');
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %s

..                                                                  2 / 2 (100%)

Time: %s, Memory: %s

Example (PhpunitDoubleColonExtension\Tests\Example)
 ✔ One
 ✔ Two

OK (2 tests, 2 assertions)
--EXPECT_EXIT_CODE--
0
