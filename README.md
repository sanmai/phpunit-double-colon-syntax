# Double Colon Syntax for PHPUnit

PHPUnit doesn't natively support the `file::method` syntax commonly used in other testing frameworks, such as pytest (`pytest test_file.py::test_method`). However, I found that AI assistants frequently suggest this syntax and even [strongly believe](https://tonsky.me/blog/gaslight-driven-development/) this syntax is a thing ([ChatGPT](https://chatgpt.com/s/t_687dfae8a4a481919793103c446e4d4f), [Gemini](https://g.co/gemini/share/402ff27b5910), [Claude](https://claude.ai/share/a1a12793-0eeb-4214-9d0a-87de3d4b5de2)), which is bonkers.

So it was always frustrating to see how an assistant generates a command using this syntax and fails, day after day, wasting time, tokens, context window, and my attention. Not anymore.

Now everyone can run individual test methods using the familiar `file::method` syntax:

```bash
vendor/bin/phpunit tests/ExampleTest.php::testItWorks
```

But before...

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
