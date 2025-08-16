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

namespace PhpunitDoubleColonExtension;

use function implode;
use function preg_match;
use function strpos;
use function strrpos;
use function substr;
use function in_array;

/**
 * @final
 */
class ArgumentTransformer
{
    private function __construct() {}

    /**
     * Whenever args can be transformed.
     * @param array<int, string> $argv
     * @return bool
     */
    public static function canTransform(array $argv): bool
    {
        foreach ($argv as $arg) {
            if (0 === strpos($arg, '--filter')) {
                return false;
            }
        }

        foreach ($argv as $arg) {
            if (preg_match('/Test\.php::.+/', $arg)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Transform file::method syntax into file + --filter method syntax.
     *
     * @param array<int, string> $argv
     * @return array<int, string>
     */
    public static function transform(array $argv): array
    {
        if (!self::canTransform($argv)) {
            return $argv;
        }

        $transformedArgv = [];
        $filterMethods = [];
        $files = [];

        foreach ($argv as $arg) {
            // Skip option flags (starting with -)
            if (0 === strpos($arg, '-')) {
                $transformedArgv[] = $arg;
                continue;
            }

            // Check for double colon syntax - must be at the end for file::method
            if (false === strpos($arg, '::')) {
                // Regular argument, pass through
                $transformedArgv[] = $arg;
                continue;
            }

            // Find the last occurrence of :: to handle paths with escaped colons
            /** @var int $lastColonPos */
            $lastColonPos = strrpos($arg, '::');

            $file = substr($arg, 0, $lastColonPos);
            $method = substr($arg, $lastColonPos + 2);

            // Only treat as file::method if method part looks like a valid method name
            // (alphanumeric, underscore, no slashes or other path characters)
            if ('' === $method || !preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $method)) {
                // Not a valid method name, treat as regular path
                $transformedArgv[] = $arg;
                continue;
            }

            // Collect unique files
            if (!in_array($file, $files, true)) {
                $files[] = $file;
            }

            // Collect method for filter
            $filterMethods[] = $method;
        }

        // Add collected files to argv
        foreach ($files as $file) {
            $transformedArgv[] = $file;
        }

        // If we found methods or have existing filter, add combined filter
        if ([] !== $filterMethods) {
            $transformedArgv[] = '--filter';
            $transformedArgv[] = implode('|', $filterMethods);
        }

        return $transformedArgv;
    }

    /**
     * Check if we're running PHPUnit via vendor/bin/phpunit.
     */
    public static function isPhpUnitExecution(string $programName): bool
    {
        return false !== strpos($programName, 'vendor/bin/phpunit');
    }
}
