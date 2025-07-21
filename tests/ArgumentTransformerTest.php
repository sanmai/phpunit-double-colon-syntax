<?php

/**
 * Copyright (c) 2025, Alexey Kopytko. All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice, this
 *    list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 *
 * 3. Neither the name of the copyright holder nor the names of its
 *    contributors may be used to endorse or promote products derived from
 *    this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 */

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use PhpunitDoubleColonExtension\ArgumentTransformer;

use function array_slice;

#[CoversClass(ArgumentTransformer::class)]
final class ArgumentTransformerTest extends TestCase
{
    public static function provide_can_transform(): iterable
    {
        yield [false, ['::']];
        yield [false, ['--filter']];
        yield [false, ['FooTest.php']];
        yield [false, ['--filter', 'FooTest.php']];
        yield [false, ['FooTest.php::']];
        yield [false, ['--filter', 'baz', 'FooTest.php::bar']];
        yield [true, ['FooTest.php::bar']];
    }

    #[DataProvider('provide_can_transform')]
    public function test_can_transform(bool $expected, array $input): void
    {
        $this->assertSame($expected, ArgumentTransformer::canTransform($input));
    }

    public function test_argv_transformation_with_double_colon_syntax(): void
    {
        $argv = ['vendor/bin/phpunit', 'tests/ExampleTest.php::test_this_one_runs'];
        $result = ArgumentTransformer::transform($argv);

        $this->assertContains('tests/ExampleTest.php', $result);
        $this->assertContains('--filter', $result);
        $this->assertContains('test_this_one_runs', $result);
        $this->assertNotContains('tests/ExampleTest.php::test_this_one_runs', $result);
    }

    public function test_argv_ignores_normal_arguments(): void
    {
        $originalArgs = ['vendor/bin/phpunit', 'tests/ExampleTest.php', '--verbose'];
        $result = ArgumentTransformer::transform($originalArgs);

        $this->assertSame($originalArgs, $result);
    }

    public function test_argv_ignores_options_with_double_colon(): void
    {
        $originalArgs = ['vendor/bin/phpunit', '--some-option=value', 'tests/ExampleTest.php'];
        $result = ArgumentTransformer::transform($originalArgs);

        $this->assertSame($originalArgs, $result);
    }

    public function test_multiple_double_colon_methods(): void
    {
        $argv = ['vendor/bin/phpunit', '--debug', 'tests/ExampleTest.php::test_one', 'tests/ExampleTest.php::test_two'];
        $result = ArgumentTransformer::transform($argv);

        $expected = [
            'vendor/bin/phpunit',
            '--debug',
            'tests/ExampleTest.php',
            '--filter',
            'test_one|test_two',
        ];

        $this->assertSame($expected, $result);
    }

    public function test_existing_filter_ignored(): void
    {
        $argv = ['vendor/bin/phpunit', '--filter', 'existing', 'tests/ExampleTest.php::test_method'];
        $result = ArgumentTransformer::transform($argv);

        $this->assertSame($argv, $result);
    }

    public function test_existing_filter_without_argument(): void
    {
        $argv = ['vendor/bin/phpunit', 'tests/ExampleTest.php', '--filter'];
        $result = ArgumentTransformer::transform($argv);

        $this->assertSame($argv, $result);
    }

    public function test_existing_filter_equals(): void
    {
        $argv = ['vendor/bin/phpunit', '--filter="foo"'];
        $result = ArgumentTransformer::transform($argv);

        $this->assertSame($argv, $result);
    }

    public function test_existing_configuration(): void
    {
        $argv = ['vendor/bin/phpunit', '--configuration="phpunit.xml"'];
        $result = ArgumentTransformer::transform($argv);

        $this->assertSame($argv, $result);
    }

    public function test_paths_with_escaped_colons_are_not_transformed(): void
    {
        $argv = ['vendor/bin/phpunit', 'tests/Example\:\:Directory/ExampleTest.php'];
        $result = ArgumentTransformer::transform($argv);

        // Should remain unchanged - not treated as file::method
        $this->assertSame($argv, $result);
    }

    public function test_invalid_method_names_are_not_transformed(): void
    {
        $argv = ['vendor/bin/phpunit', 'tests/ExampleTest.php::invalid/method'];
        $result = ArgumentTransformer::transform($argv);

        // Should remain unchanged - slash makes it invalid method name
        $this->assertSame($argv, $result);
    }

    public function test_empty_method_name_not_transformed(): void
    {
        $argv = ['vendor/bin/phpunit', 'tests/ExampleTest.php::'];
        $result = ArgumentTransformer::transform($argv);

        // Should remain unchanged - empty method name
        $this->assertSame($argv, $result);
    }

    public function test_invalid_method_names_mixed_with_valid(): void
    {
        $argv = ['vendor/bin/phpunit', 'tests/ExampleTest.php::', 'tests/ExampleTest.php::invalid/method', 'tests/ExampleTest.php::foo'];
        $result = ArgumentTransformer::transform($argv);

        $expected = [...array_slice($argv, 0, -1), 'tests/ExampleTest.php', '--filter', 'foo'];
        $this->assertSame($expected, $result);
    }

    public function test_multiple_colons_uses_last_occurrence(): void
    {
        $argv = ['vendor/bin/phpunit', 'tests/Some::Complex::Path/ExampleTest.php::test_method'];
        $result = ArgumentTransformer::transform($argv);

        $expected = [
            'vendor/bin/phpunit',
            'tests/Some::Complex::Path/ExampleTest.php',
            '--filter',
            'test_method',
        ];

        $this->assertSame($expected, $result);
    }

    public function test_exact_failing_scenario_with_dot_slash(): void
    {
        $argv = ['./vendor/bin/phpunit', 'tests/ExampleTest.php::test_one', 'tests/ExampleTest.php::test_two'];
        $result = ArgumentTransformer::transform($argv);

        $expected = [
            './vendor/bin/phpunit',
            'tests/ExampleTest.php',
            '--filter',
            'test_one|test_two',
        ];

        $this->assertSame($expected, $result, 'Multiple methods should produce regex filter pattern');
    }

    public function test_single_method_no_regex_delimiters(): void
    {
        // Single method should NOT use regex delimiters
        $argv = ['vendor/bin/phpunit', 'tests/ExampleTest.php::test_one'];
        $result = ArgumentTransformer::transform($argv);

        $expected = [
            'vendor/bin/phpunit',
            'tests/ExampleTest.php',
            '--filter',
            'test_one',
        ];

        $this->assertSame($expected, $result, 'Single method should not use regex delimiters');
    }

    public function test_is_phpunit(): void
    {
        $this->assertTrue(ArgumentTransformer::isPhpUnitExecution('vendor/bin/phpunit'));
        $this->assertTrue(ArgumentTransformer::isPhpUnitExecution('./vendor/bin/phpunit'));
        $this->assertFalse(ArgumentTransformer::isPhpUnitExecution('phpunit.phar'));
        $this->assertFalse(ArgumentTransformer::isPhpUnitExecution('foobar'));
    }
}
