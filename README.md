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

To sync a code example with a source file, add a comment before your code block. The language identifier (e.g., `php`, `graphql`, `javascript`) is automatically inferred from the file extension:

````markdown
<!-- source: examples/demo.php -->
```php
// This code will be replaced with content from examples/demo.php
```
````

The hook supports a wide range of file extensions including `.php`, `.graphql`, `.gql`, `.js`, `.ts`, `.json`, `.yml`, `.yaml`, `.sql`, and many more.

### Syncing Output (Optional)

To show the output of executing a PHP file, use:

````markdown
<!-- output: examples/demo.php -->
```php
// This will be replaced with the output from executing examples/demo.php
```
````

## Example

Here's how it looks in practice: [ruudk/code-generator](https://github.com/ruudk/code-generator).

## 💖 Support This Project

Love this tool? Help me keep building awesome open source software!

[![Sponsor](https://img.shields.io/badge/Sponsor-%E2%9D%A4-pink)](https://github.com/sponsors/ruudk)

Your sponsorship helps me dedicate more time to maintaining and improving this project. Every contribution, no matter the size, makes a difference!

## 🤝 Contributing

I welcome contributions! Whether it's a bug fix, new feature, or documentation improvement, I'd love to see your PRs.

## 📄 License

MIT License – Free to use in your projects! If you're using this and finding value, please consider [sponsoring](https://github.com/sponsors/ruudk) to support continued development.

