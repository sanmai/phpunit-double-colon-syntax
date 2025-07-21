--TEST--
PHPUnit finds the only one test
--FILE--
<?php
passthru('./vendor/bin/phpunit --colors=never tests/ExampleTest.php::testItWorks');
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %s
Random Seed: %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

Example (PhpunitDoubleColonExtension\Tests\Example)
 ✔ It works

OK (1 test, 1 assertion)
--EXPECT_EXIT_CODE--
0
