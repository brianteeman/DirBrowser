# DirBrowser

A modern, developer-friendly replacement for Apache directory listings.

DirBrowser is a lightweight PHP-based directory browser designed for local development environments, especially [Laragon](https://laragon.org/). It provides a clean, modern interface for browsing folders that do not contain an `index.php`, `index.html`, or other index file.

Instead of seeing Apache's plain directory listing, DirBrowser gives you a GitHub-inspired browsing experience with file icons, metadata, search, sorting, README rendering, and a developer-friendly interface.

DirBrowser requires no database, no framework, and no build process. Installation is simply copying one folder and adding a small Apache configuration change.

---

## Features

### Modern directory browsing

* Clean Bootstrap 5 interface
* Folder-first display
* File type icons
* File sizes
* Last modified dates
* Breadcrumb navigation

### Developer-friendly tools

* Instant file search
* Click column headers to sort
* `Ctrl + /` keyboard shortcut to focus search
* Dark mode toggle
* Responsive layout

### README support

If a folder contains a:

```
README.md
```

file, DirBrowser automatically displays it underneath the directory listing.

Supported Markdown features include:

* Headings
* Bold text
* Inline code
* Code blocks
* Lists
* Links

README content is safely escaped before rendering.

### Laragon integration

Designed specifically for Laragon workflows:

* Works with `.test` virtual hosts
* Works with multiple local projects
* Appears only when no index file exists
* Does not interfere with normal websites
* Requires only a single PHP file

---

# Requirements

* PHP 8.1 or newer
* Apache
* Laragon (recommended)

---

# Installation with Laragon

## 1. Copy DirBrowser

Create the following folder:

```
C:\laragon\usr\dirbrowser
```

Copy:

```
index.php
```

into that folder:

```
C:\laragon\usr\dirbrowser\index.php
```

Your structure should look like:

```
C:\laragon
│
├── usr
│   └── dirbrowser
│       └── index.php
│
└── www
    ├── project1
    ├── project2
    └── downloads
```

---

## 2. Create the Apache configuration

Create:

```
C:\laragon\etc\apache2\extra\httpd-dirbrowser.conf
```

Add:

```apache
#
# DirBrowser
#

Alias /__dirbrowser "C:/laragon/usr/dirbrowser"


<Directory "C:/laragon/usr/dirbrowser">

    AllowOverride None
    Require all granted

</Directory>


#
# Show DirBrowser instead of Apache directory listing
#

<Directory "C:/laragon/www">

    Options -Indexes

    ErrorDocument 403 /__dirbrowser/index.php

</Directory>
```

---

## 3. Enable the configuration

Open your Apache configuration:

```
C:\laragon\bin\apache\httpd-*\conf\httpd.conf
```

Find the other `Include` statements and add:

```apache
Include conf/extra/httpd-dirbrowser.conf
```

Save the file.

---

## 4. Restart Apache

In Laragon:

```
Menu
 → Apache
 → Restart
```

---

# Testing

Create a folder without an index file:

```
C:\laragon\www\testbrowser
```

Add some files:

```
testbrowser
│
├── example.txt
├── image.png
└── archive.zip
```

Visit:

```
http://testbrowser.test/
```

Instead of Apache's directory listing, DirBrowser will appear.

---

Now add:

```
index.php
```

to the folder:

```php
<?php

echo "Hello Laragon";
```

Refresh the browser.

Your PHP file will load normally and DirBrowser will no longer appear.

---

# Adding README files

To display documentation for a folder, simply add:

```
README.md
```

Example:

```
my-project
│
├── README.md
├── index.php
└── assets
```

The README will automatically appear below the directory listing.

---

# Configuration

Basic settings are available at the top of `index.php`:

```php
$config = [
    'showHiddenFiles' => false,
    'showFileSizes'   => true,
    'showModified'    => true,
    'showReadme'      => true,
    'darkMode'        => false,
];
```

Options:

| Option            | Description                   |
| ----------------- | ----------------------------- |
| `showHiddenFiles` | Show files beginning with `.` |
| `showFileSizes`   | Display file sizes            |
| `showModified`    | Display modified dates        |
| `showReadme`      | Render README.md files        |
| `darkMode`        | Start in dark mode            |

---

# License

DirBrowser is released under the MIT License.

---

# Credits

Built for developers who want a better directory browsing experience during local development.

Inspired by modern directory indexers and GitHub-style project browsing.
