# README Examples Sync Hook

A [CaptainHook](https://github.com/captainhook-git/captainhook) action that automatically syncs PHP code examples in your README.md with actual source files. This ensures your documentation always shows up-to-date, working code examples.

## Installation

```bash
composer require --dev ruudk/readme-examples-sync-hook
```

## Configuration

Add the hook to your `captainhook.json` configuration file in the `pre-commit` section:

```json
{
    "pre-commit": {
        "enabled": true,
        "actions": [
            {
                "action": "\\Ruudk\\ReadmeExamplesSyncHook\\SyncReadmeExamples"
            }
        ]
    }
}
```

## How It Works

This hook scans your README.md file for special HTML comments that mark code examples:

1. **Source code sync**: Updates code blocks marked with `<!-- source: ... -->` with the actual content from source files
2. **Output sync** (optional): When you add `<!-- output: ... -->` comments, the hook executes PHP files and captures their output to display results

The hook automatically stages the updated README.md if changes are detected, ensuring your documentation stays in sync with your code.

## Usage

### Syncing Source Code

To sync a code example with a source file, add a comment before your code block:

```markdown
<!-- source: examples/demo.php -->
```php
// This code will be replaced with content from examples/demo.php
```
```

### Syncing Output (Optional)

To show the output of executing a PHP file, use:

```markdown
<!-- output: examples/demo.php -->
```php
// This will be replaced with the output from executing examples/demo.php
```
```

## Example

Here's how it looks in practice (from the [ruudk/code-generator](https://github.com/ruudk/code-generator) project):

**In your README.md:**
```markdown
## Example

<!-- source: examples/class.php -->
```php
// Code will be synced here
```

### Output

<!-- output: examples/class.php -->
```php
// Output will be synced here
```
```

**Result after sync:**
The hook automatically replaces the code blocks with:
- The actual source code from `examples/class.php`
- The output generated when executing `examples/class.php`

## Features

- **Automatic path adjustments**: Converts relative autoload paths for README display
- **Clean output formatting**: Removes unnecessary whitespace and formatting issues
- **Git integration**: Automatically stages README changes when updates are detected
- **Verbose logging**: Provides detailed feedback during the sync process
- **Safe execution**: Uses temporary files for executing examples

## License

MIT
