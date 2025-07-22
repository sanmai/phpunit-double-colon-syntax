--TEST--
PHPUnit finds the only one test
--FILE--
<?php
ob_start();
passthru('./vendor/bin/phpunit tests/ExampleTest.php::testItWorks');
passthru('./vendor/bin/phpunit tests/ExampleTest.php::test_one tests/ExampleTest.php::test_two');

preg_match_all('/OK.*/', ob_get_clean(), $matches);

foreach ($matches[0] as $line) {
    echo "$line\n";
}
?>
--EXPECTF--
OK (1 test, 1 assertion)
OK (2 tests, 2 assertions)
