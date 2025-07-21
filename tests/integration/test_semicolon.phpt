--TEST--
PHPUnit finds the tests in semicolon directory
--FILE--
<?php
passthru('./vendor/bin/phpunit --colors=never "tests/Example::Directory/ExampleTest.php::test_method"');
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %s
Random Seed: %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

Example (PhpunitDoubleColonExtension\Tests\Example\Example)
 ✔ Method without name

OK (1 test, 1 assertion)
--EXPECT_EXIT_CODE--
0
