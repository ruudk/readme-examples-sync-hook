# README Examples Sync

A PHP script that automatically syncs code examples in your README.md with actual source files.
This ensures your documentation always shows up-to-date, working code examples.

## Installation

```bash
composer require --dev ruudk/readme-examples-sync-hook
```

## Usage

Run the script from your project root:

```bash
vendor/bin/readme-examples-sync
```

### Git Hook Integration

The easiest way to automatically sync your README on commit is using [Lefthook](https://lefthook.dev):

1. Install Lefthook (if not already installed):
   ```bash
   # macOS
   brew install lefthook
   ```

2. Create a `lefthook.yml` file in your project root:
   ```yaml
   pre-commit:
     parallel: false
     commands:
       sync-readme-examples:
         glob:
           - "*.php"
           - "*.md"
         run: vendor/bin/readme-examples-sync
         stage_fixed: true
   ```

3. Install the hooks:
   ```bash
   lefthook install
   ```

That's it! Now your README will automatically sync whenever you commit changes to PHP or Markdown files.

#### Alternative: Manual Git Hook

If you prefer not to use Lefthook, you can manually create a `.git/hooks/pre-commit` file:

```bash
#!/bin/bash
vendor/bin/readme-examples-sync
```

Don't forget to make the hook executable:

```bash
chmod +x .git/hooks/pre-commit
```

## How It Works

This script scans your README.md file for special HTML comments that mark code examples:

1. **Source code sync**: Updates code blocks marked with `<!-- source: ... -->` with the actual content from source files
2. **Output sync** (optional): When you add `<!-- output: ... -->` comments, the script executes PHP files and captures their output to display results

The script automatically stages the updated README.md if changes are detected (when run in a git repository), ensuring your documentation stays in sync with your code.

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

