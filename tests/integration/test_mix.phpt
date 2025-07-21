--TEST--
PHPUnit finds tests - mix and match
--FILE--
<?php
passthru('./vendor/bin/phpunit --order-by=default --colors=never --filter testItWorks tests/ExampleTest.php::test_method');
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Test file "tests/ExampleTest.php::test_method" not found
--EXPECT_EXIT_CODE--
0
