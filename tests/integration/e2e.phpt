--TEST--
PHPUnit finds the only one test
--FILE--
<?php
ob_start();
passthru('./vendor/bin/phpunit tests/ExampleTest.php::testItWorks 2>/dev/null');
passthru('./vendor/bin/phpunit tests/ExampleTest.php::test_one tests/ExampleTest.php::test_two 2>/dev/null');

preg_match_all('/OK.*/', ob_get_clean(), $matches);

foreach ($matches[0] as $line) {
    echo "$line\n";
}
?>
--EXPECTF--
OK (1 test, 1 assertion)
OK (2 tests, 2 assertions)
