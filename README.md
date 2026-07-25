# DirBrowser

A modern, lightweight replacement for Apache directory listings.

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

## Option 1 — Single project installation

Copy `index.php` into any project folder.

Example:

```
C:\laragon\www\my-project\
│
└── index.php
```

Start Laragon and open:

```
http://my-project.test/
```

If the folder does not contain:

```
index.php
index.html
index.htm
```

DirBrowser will display the directory contents.

---

## Option 2 — Global directory browser

If you want DirBrowser available for multiple projects, create:

```
C:\laragon\usr\dirbrowser\
│
└── index.php
```

Then configure Apache to use it as the directory listing handler.

Add:

```apache
ErrorDocument 403 /__dirbrowser/index.php
```

Restart Apache.

Now folders without an index file can automatically display DirBrowser.

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
