# Double Colon Syntax for PHPUnit

Run individual test methods using `file::method` syntax:

```bash
vendor/bin/phpunit tests/ExampleTest.php::testItWorks
```

## Installation

```bash
composer require --dev sanmai/phpunit-double-colon-syntax
```

That's it! No configuration needed.

## Usage

It works with multiple methods:

```bash
vendor/bin/phpunit tests/ExampleTest.php::test_one tests/ExampleTest.php::test_two
```

It does not work with `--filter`: you have to choose this or that syntax, not both.

## How It Works

Uses Composer's autoloader to intercept and transform arguments before PHPUnit starts. The `file::method` syntax becomes `file --filter method` automatically. It targets specifically `vendor/bin/phpunit` and ignores everything else.
