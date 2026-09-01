# DirBrowser

DirBrowser 2.0 is a modern, lightweight replacement for Apache directory listings with one-click file running and previews.

DirBrowser provides a clean, GitHub-inspired file browser for folders that do not contain an `index.*` file. It is designed primarily for local development environments such as **Laragon**, where browsing project folders is often more useful than seeing Apache's default directory listing.

DirBrowser is a single PHP file with no installation process, no database, and no external dependencies.

Simply place `index.php` in a folder and open it through your browser.

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
- Click files to run or preview them
- Image files open in a lightbox/modal
- Text, Markdown, JavaScript, CSS, JSON, XML, and log files open in a lightbox/modal
- PHP files open directly so your PHP server executes them normally
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


### Run and preview files

DirBrowser 2.0 turns each file name into an action:

- PHP files open in the browser as normal links, allowing Apache/PHP to execute them just like any regular PHP page.
- Images open in a built-in lightbox/modal instead of navigating away from the directory listing.
- Text-oriented files, including JavaScript, Markdown, CSS, JSON, XML, logs, CSV, YAML, INI, ENV, and SQL files, open in a readable modal preview.
- HTML files open in the browser as normal links so the browser renders them instead of showing their source in a modal.
- Other file types remain normal links so the browser can download or open them according to its default behavior.

Press `Escape`, click the backdrop, or use the close button to dismiss a preview.

---

### Sorting

Columns can be sorted by clicking the heading:

- Name
- Size
- Modified date

The active sort column displays an arrow showing:

- ascending order
- descending order

When sorting by name:

- folders remain grouped together
- files remain grouped together
- alphabetical sorting happens within each group

---

### Search

Instant client-side searching.

Press:

```
Ctrl + /
```

to focus the search box.

Press:

```
Escape
```

to clear the search.

---

### Dark mode

Includes a built-in dark mode toggle.

The selected theme is remembered using browser local storage, so your preference remains after refreshing the page.

---

### README.md support

If a folder contains:

```
README.md
```

DirBrowser automatically displays it underneath the directory listing.

This is useful for:

- project documentation
- installation notes
- development information
- repository descriptions

---

### File icons

DirBrowser includes inline SVG icons for common file types:

- PHP
- HTML
- CSS
- JavaScript
- JSON
- XML
- Markdown
- Images
- Archives
- PDF files

---

### Security features

DirBrowser includes basic protections:

- prevents directory traversal
- hides hidden files by default
- blocks access outside the document root
- escapes displayed filenames and paths

---

## Requirements

- PHP 8.1 or newer
- Apache (recommended)
- Laragon (recommended for local development)

---

# Installation with Laragon

## Global installation

DirBrowser can be installed once and used by Apache/Laragon as a global directory-index fallback. In this mode you keep one copy at:

```text
C:\laragon\usr\dirbrowser\index.php
```

and Apache uses it whenever a requested directory does not contain a normal index file.

Copy these repository files to the same folder:

```text
C:\laragon\usr\dirbrowser\index.php
C:\laragon\usr\dirbrowser\laragon-dirbrowser.conf
```

The Apache configuration file is included in this repository as `laragon-dirbrowser.conf`.

Open Laragon's Apache `httpd.conf`. You can reach it from Laragon with **Menu** → **Apache** → **httpd.conf**.

Find the existing commented Fancy directory listings block:

```apache
# Fancy directory listings
#Include conf/extra/httpd-autoindex.conf
```

Leave `httpd-autoindex.conf` commented unless you specifically want Apache's built-in fancy indexes. Add the DirBrowser include below that block:

```apache
# Fancy directory listings
#Include conf/extra/httpd-autoindex.conf
Include "C:/laragon/usr/dirbrowser/laragon-dirbrowser.conf"
```

This changes the main Apache configuration once and avoids editing Laragon-generated virtual-host files, which may be regenerated.

The provided configuration uses Apache's `DirectoryIndex` mechanism:

```apache
DirectoryIndex index.php index.html index.htm /__dirbrowser__
Alias /__dirbrowser__ "C:/laragon/usr/dirbrowser/index.php"
```

Because `index.php`, `index.html` and `index.htm` come before `/__dirbrowser__`, normal index files continue to take precedence. DirBrowser is only used when those files are absent.

Restart Apache from Laragon after adding or removing the include:

1. Open Laragon.
2. Choose **Menu** → **Apache** → **Restart**.

To test global mode, create directories without index files in more than one virtual host, for example:

```text
C:\laragon\www\site1\foo\example.txt
C:\laragon\www\site1\foo\nested\nested.txt
C:\laragon\www\site2\foo\example.txt
```

Then open:

```text
https://site1.test/foo/
https://site1.test/foo/nested/
https://site2.test/foo/
```

Each URL should browse the matching directory under that host's own `DOCUMENT_ROOT`.

To confirm normal index-file precedence, create:

```text
C:\laragon\www\site1\has-index\index.php
```

and open:

```text
https://site1.test/has-index/
```

Apache should serve that local `index.php`, not DirBrowser.

To disable global integration, comment or remove the include line and restart Apache:

```apache
# Include "C:/laragon/usr/dirbrowser/laragon-dirbrowser.conf"
```

---

# Testing

You can test DirBrowser without any Apache configuration.

Create:

```
C:\laragon\www\test-folder\
│
├── index.php
├── README.md
├── example.txt
└── images\
```

Open:

```
http://test-folder.test/
```

The directory contents will be displayed.

---

# Configuration

Configuration is located near the top of `index.php`.

Example:

```php
$config = [
    'showHiddenFiles' => false,
    'showFileSizes'   => true,
];
```

Options:

| Option | Description |
|---|---|
| `showHiddenFiles` | Display hidden files such as `.git` files |
| `showFileSizes` | Display file sizes |

---

# Why DirBrowser?

Apache's default directory listing is functional but limited.

DirBrowser provides:

- a more pleasant browsing experience
- useful project documentation
- better visibility of development files
- a modern interface for local development

It is especially useful when working with:

- Laravel projects
- Joomla extensions
- WordPress plugins
- static websites
- JavaScript projects
- documentation folders
- build output directories

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

Copyright © @brianteeman
