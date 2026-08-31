# DirBrowser

A modern, lightweight replacement for Apache directory listings.

DirBrowser provides a clean, GitHub-inspired file browser for folders that do not contain an `index.*` file. It is designed primarily for local development environments such as **Laragon**, where browsing project folders is often more useful than seeing Apache's default directory listing.

DirBrowser is a single PHP file with no installation process, no database, and no external dependencies.

You can either place `index.php` directly in a folder, or install one global copy for Laragon/Apache.

> [!WARNING]
> **DirBrowser is designed for local development environments only.**
>
> This tool is intended for use on local development servers such as **Laragon**, where you need a convenient way to browse project folders during development.
>
> **Do not install DirBrowser on a live production server.**

---

## Features

### Modern directory browsing

- Clean, responsive interface
- Folder and file listing
- Folders displayed before files
- File metadata:
  - size
  - modified date
- Breadcrumb navigation
- Parent directory navigation

---

### GitHub-inspired design

The interface is inspired by modern code hosting platforms:

- clean typography
- inline SVG icons
- familiar folder and file presentation
- readable paths
- responsive layout

---

### Sorting

Columns can be sorted by clicking the heading:

- Name
- Size
- Modified date

The active sort column displays an arrow showing ascending or descending order.

When sorting by name, folders remain grouped together and alphabetical sorting happens within each group.

---

### Search

Instant client-side searching.

Press `Ctrl + /` to focus the search box. Press `Escape` to clear the search.

---

### Dark mode

Includes a built-in dark mode toggle.

The selected theme is remembered using browser local storage, so your preference remains after refreshing the page.

---

### README.md support

If a folder contains `README.md`, DirBrowser automatically displays it underneath the directory listing.

---

### Security features

DirBrowser includes protections for global mode:

- canonicalises requested filesystem paths with `realpath()`
- rejects `..`, encoded traversal and direct endpoint requests
- blocks symlink escapes outside the active virtual host `DOCUMENT_ROOT`
- hides hidden files by default
- escapes displayed filenames and paths

---

## Requirements

- PHP 8.1 or newer
- Apache (recommended)
- Laragon (recommended for local development)

---

## Direct installation

Place `index.php` in the folder you want to browse and open that folder through Apache/Laragon.

Example:

```text
C:\laragon\www\test-folder\
│
├── index.php
├── README.md
├── example.txt
└── images\
```

Open:

```text
http://test-folder.test/
```

DirBrowser will browse the directory containing that `index.php` file.

---

## Global installation with Laragon

Global mode lets you keep exactly one copy of DirBrowser at:

```text
C:\laragon\usr\dirbrowser\index.php
```

Apache/Laragon then uses that single file only when a requested directory does not contain a normal index file.

### 1. Install the files

Create the directory:

```text
C:\laragon\usr\dirbrowser\
```

Copy these repository files into it:

```text
C:\laragon\usr\dirbrowser\index.php
C:\laragon\usr\dirbrowser\laragon-dirbrowser.conf
```

The Apache configuration is included in this repository at:

```text
apache/laragon-dirbrowser.conf
```

### 2. Include the Apache configuration

Add this line to a user-maintained Apache configuration file that Laragon loads:

```apache
Include "C:/laragon/usr/dirbrowser/laragon-dirbrowser.conf"
```

One practical approach is to create a small file under Laragon's user area, for example:

```text
C:\laragon\usr\dirbrowser\include-dirbrowser.conf
```

and include that file from Apache's main configuration once. Avoid editing Laragon-generated virtual host files because Laragon may regenerate them.

The provided configuration uses Apache's `DirectoryIndex` fallback:

```apache
DirectoryIndex index.php index.html index.htm /__dirbrowser__
Alias /__dirbrowser__ "C:/laragon/usr/dirbrowser/index.php"
```

Because `index.php`, `index.html` and `index.htm` appear before `/__dirbrowser__`, normal project index files continue to take precedence. DirBrowser is only invoked as the final fallback.

### 3. Restart Apache

Restart Apache from Laragon:

1. Open the Laragon window.
2. Click **Menu**.
3. Choose **Apache**.
4. Click **Restart**.

### 4. Test global mode

Create two Laragon projects:

```text
C:\laragon\www\site1\foo\example.txt
C:\laragon\www\site1\foo\nested\nested.txt
C:\laragon\www\site2\foo\example.txt
```

Open these URLs:

```text
https://site1.test/foo/
https://site1.test/foo/nested/
https://site2.test/foo/
```

Expected results:

- `https://site1.test/foo/` browses `C:\laragon\www\site1\foo\`.
- `https://site1.test/foo/nested/` browses `C:\laragon\www\site1\foo\nested\`.
- `https://site2.test/foo/` browses `C:\laragon\www\site2\foo\`.

The same `/foo/` URL path resolves independently for each virtual host because DirBrowser resolves the original request relative to that virtual host's `DOCUMENT_ROOT`.

### 5. Verify normal index files still win

Create:

```text
C:\laragon\www\site1\has-index\index.php
```

Open:

```text
https://site1.test/has-index/
```

Apache should serve `has-index\index.php`, not DirBrowser.

### 6. Disable global integration

Remove or comment out the include line:

```apache
# Include "C:/laragon/usr/dirbrowser/laragon-dirbrowser.conf"
```

Then restart Apache from Laragon.

---

## Test/demo procedure

A lightweight self-test is available for the path-resolution and security-sensitive parts of global mode:

```bash
php index.php --self-test
```

It verifies:

- a directory without an index resolves to the browsed directory
- nested directories resolve correctly
- multiple virtual hosts use their own document roots
- `..` traversal is rejected
- URL-encoded traversal is rejected
- symlink escapes outside the document root are rejected
- direct access to `/__dirbrowser__` is rejected

Apache itself verifies native index precedence through the `DirectoryIndex` order: `index.php index.html index.htm /__dirbrowser__`.

---

# Configuration

Configuration is located near the top of `index.php`.

Example:

```php
$config = [
    'showHiddenFiles' => false,
    'showReadme'      => true,
    'globalEndpoint'  => '/__dirbrowser__',
];
```

Options:

| Option | Description |
|---|---|
| `showHiddenFiles` | Display hidden files such as `.git` files |
| `showReadme` | Display README.md content below the directory listing |
| `globalEndpoint` | Internal Apache endpoint used by the global DirectoryIndex fallback |

---

# Why DirBrowser?

Apache's default directory listing is functional but limited.

DirBrowser provides:

- a more pleasant browsing experience
- useful project documentation
- better visibility of development files
- a modern interface for local development

It is especially useful when working with Laravel projects, Joomla extensions, WordPress plugins, static websites, JavaScript projects, documentation folders and build output directories.

---

# Credits

Inspired by:

- Apache Fancy Indexing projects
- GitHub repository browsing
- modern developer tools

Built as a lightweight alternative for local development environments.

---

# License

MIT License
