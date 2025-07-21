--TEST--
PHPUnit exits with non-zero code when no tests are executed (failOnEmptyTestSuite)
--FILE--
<?php
$command = './vendor/bin/phpunit --colors=never tests/ExampleTest.php::bogus';
exec($command, $output, $exitCode);
echo end($output);
exit($exitCode);
?>
--EXPECTF--
No tests executed!
--EXPECT_EXIT_CODE--
1
