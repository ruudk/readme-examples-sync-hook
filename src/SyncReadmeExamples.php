<?php

declare(strict_types=1);

namespace Ruudk\ReadmeExamplesSyncHook;

final class SyncReadmeExamples
{
    /**
     * Sync README.md with example files
     *
     * @return int Exit code (0 for success, 1 for error)
     */
    public function sync(string $repositoryRoot) : int
    {
        $readmePath = $repositoryRoot . '/README.md';

        if ( ! file_exists($readmePath)) {
            $this->writeOutput('README.md not found, skipping sync');

            return 0;
        }

        $this->writeOutput('Syncing README.md with example files...');

        $readme = file_get_contents($readmePath);

        if ($readme === false) {
            $this->writeError('Failed to read README.md');

            return 1;
        }

        $updatedReadme = $this->syncReadmeWithExamples($readme, $repositoryRoot);

        if ($readme !== $updatedReadme) {
            file_put_contents($readmePath, $updatedReadme);
            $this->writeOutput('✓ README.md has been synced with example files');

            // Stage the README changes if in a git repository
            if (is_dir($repositoryRoot . '/.git')) {
                exec('git add README.md');
            }
        } else {
            $this->writeOutput('✓ README.md is already in sync');
        }

        return 0;
    }

    /**
     * Sync README content with example files
     */
    private function syncReadmeWithExamples(string $readme, string $repositoryRoot) : string
    {
        $lines = explode("\n", $readme);
        $result = [];
        $i = 0;

        while ($i < count($lines)) {
            $line = $lines[$i];

            // Check for source comment
            if (preg_match('/^<!-- source: (.+) -->$/', trim($line), $matches)) {
                $sourceFile = $matches[1];
                $language = $this->getLanguageFromExtension($sourceFile);
                $result[] = $line; // Keep the source comment
                ++$i;

                // Process the code block
                if ($i < count($lines) && preg_match('/^```\w*\s*$/', $lines[$i])) {
                    $result[] = '```' . $language; // Use inferred language
                    ++$i;

                    // Skip old code content until closing ```
                    while ($i < count($lines) && $lines[$i] !== '```') {
                        ++$i;
                    }

                    // Insert new code from source file
                    $code = $this->getExampleCode($sourceFile, $repositoryRoot);

                    if ($code !== null) {
                        // Add code lines without trailing newline on last line
                        $codeLines = explode("\n", rtrim($code));
                        foreach ($codeLines as $codeLine) {
                            $result[] = $codeLine;
                        }
                    }

                    if ($i < count($lines) && $lines[$i] === '```') {
                        $result[] = $lines[$i]; // Keep closing ```
                        ++$i;
                    }
                }
            }
            // Check for output comment
            elseif (preg_match('/^<!-- output: (.+) -->$/', trim($line), $matches)) {
                $sourceFile = $matches[1];
                $result[] = $line; // Keep the output comment
                ++$i;

                // Process the output code block
                if ($i < count($lines) && preg_match('/^```php\s*$/', $lines[$i])) {
                    $result[] = $lines[$i]; // Keep ```php
                    ++$i;

                    // Skip old output until closing ```
                    while ($i < count($lines) && $lines[$i] !== '```') {
                        ++$i;
                    }

                    // Insert new output from executing the file
                    $output = $this->executeExample($sourceFile, $repositoryRoot);

                    if ($output !== null) {
                        $outputLines = explode("\n", rtrim($output));
                        foreach ($outputLines as $outputLine) {
                            $result[] = $outputLine;
                        }
                    }

                    if ($i < count($lines) && $lines[$i] === '```') {
                        $result[] = $lines[$i]; // Keep closing ```
                        ++$i;
                    }
                }
            } else {
                $result[] = $line;
                ++$i;
            }
        }

        return implode("\n", $result);
    }

    /**
     * Get code from example file with path adjustments
     */
    private function getExampleCode(string $sourceFile, string $repositoryRoot) : ?string
    {
        $fullPath = $repositoryRoot . '/' . $sourceFile;

        if ( ! file_exists($fullPath)) {
            $this->writeOutput(sprintf('Warning: Source file not found: %s', $sourceFile));

            return null;
        }

        $code = file_get_contents($fullPath);

        if ($code === false) {
            $this->writeOutput(sprintf('Warning: Failed to read source file: %s', $sourceFile));

            return null;
        }

        // Replace ../vendor/autoload.php with vendor/autoload.php for README display
        $code = str_replace(
            ["include '../vendor/autoload.php'", "require '../vendor/autoload.php'"],
            ["include 'vendor/autoload.php'", "require 'vendor/autoload.php'"],
            $code,
        );

        // Remove opening <?php tag and initial empty lines
        $lines = explode("\n", $code);
        $result = [];
        $foundStart = false;

        foreach ($lines as $line) {
            if ( ! $foundStart) {
                if (trim($line) === '<?php') {
                    $result[] = '<?php';
                    $foundStart = true;
                } elseif (trim($line) !== '') {
                    $foundStart = true;
                    $result[] = $line;
                }
            } else {
                $result[] = $line;
            }
        }

        return implode("\n", $result);
    }

    /**
     * Execute example file and capture output
     */
    private function executeExample(string $sourceFile, string $repositoryRoot) : ?string
    {
        $fullPath = $repositoryRoot . '/' . $sourceFile;

        if ( ! file_exists($fullPath)) {
            $this->writeOutput(sprintf('Warning: Source file not found: %s', $sourceFile));

            return null;
        }

        // Create temporary file for execution
        $tempFile = tempnam(sys_get_temp_dir(), 'example_');

        try {
            // Copy the file content and ensure it has proper autoload path
            $code = file_get_contents($fullPath);

            if ($code === false) {
                $this->writeOutput(sprintf('Warning: Failed to read source file: %s', $sourceFile));

                return null;
            }

            // Make sure the code uses absolute path for autoload
            $code = preg_replace(
                "/(include|require|include_once|require_once)\s+['\"]\.\.\/vendor\/autoload\.php['\"]/",
                "$1 '" . $repositoryRoot . "/vendor/autoload.php'",
                $code,
            );

            if ($tempFile === false) {
                $this->writeOutput(sprintf('Warning: Failed to create temp file for: %s', $sourceFile));

                return null;
            }

            file_put_contents($tempFile, $code);

            // Execute and capture output
            $command = sprintf('php %s 2>&1', escapeshellarg($tempFile));
            $output = shell_exec($command);

            if ($output === null || $output === false) {
                $this->writeOutput(sprintf('Warning: Failed to execute: %s', $sourceFile));

                return null;
            }

            // Clean up the output
            return $this->normalizeOutput($output);
        } finally {
            if ($tempFile !== false && file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }

    /**
     * Normalize output for clean display
     */
    private function normalizeOutput(string $output) : string
    {
        // Remove trailing whitespace from each line
        $lines = explode("\n", $output);
        $lines = array_map('rtrim', $lines);

        // Remove leading/trailing empty lines
        while ( ! empty($lines) && trim($lines[0]) === '') {
            array_shift($lines);
        }

        while ( ! empty($lines) && trim($lines[count($lines) - 1]) === '') {
            array_pop($lines);
        }

        return implode("\n", $lines);
    }

    /**
     * Infer the language identifier from file extension
     */
    private function getLanguageFromExtension(string $filePath) : string
    {
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);

        return match ($extension) {
            'php' => 'php',
            'graphql', 'gql' => 'graphql',
            'js', 'mjs', 'cjs' => 'javascript',
            'ts', 'mts', 'cts' => 'typescript',
            'json' => 'json',
            'yml', 'yaml' => 'yaml',
            'xml' => 'xml',
            'sql' => 'sql',
            'sh', 'bash' => 'bash',
            'py' => 'python',
            'rb' => 'ruby',
            'go' => 'go',
            'rs' => 'rust',
            'java' => 'java',
            'c' => 'c',
            'cpp', 'cc', 'cxx' => 'cpp',
            'cs' => 'csharp',
            'swift' => 'swift',
            'kt', 'kts' => 'kotlin',
            'md', 'markdown' => 'markdown',
            'html', 'htm' => 'html',
            'css' => 'css',
            'twig' => 'twig',
            'scss', 'sass' => 'scss',
            default => '',
        };
    }

    /**
     * Write output message to stdout
     */
    private function writeOutput(string $message) : void
    {
        echo $message . PHP_EOL;
    }

    /**
     * Write error message to stderr
     */
    private function writeError(string $message) : void
    {
        fwrite(STDERR, $message . PHP_EOL);
    }
}
