<?php
/**
 * DirBrowser v1.0
 *
 * A modern replacement for Apache directory listings.
 *
 * Requirements:
 * - PHP 8.1+
 *
 * Features:
 * - Fancy directory browsing
 * - Inline SVG icons
 * - README.md rendering
 * - Search
 * - Sortable columns
 * - Dark mode
 * - Laragon friendly
 *
 * @license MIT
 */
declare(strict_types=1);
/*
|--------------------------------------------------------------------------
| Configuration
|--------------------------------------------------------------------------
*/
$config = [
    'showHiddenFiles' => false,
    'showReadme'      => true,
];
/*
|--------------------------------------------------------------------------
| Directory handling
|--------------------------------------------------------------------------
*/
$documentRoot = realpath(
    $_SERVER['DOCUMENT_ROOT'] ?? ''
);
if (!$documentRoot || !is_dir($documentRoot)) {
    http_response_code(500);
    exit('Unable to determine directory');
}
$directory = $documentRoot;
/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/
function formatBytes(int $bytes): string
{
    if ($bytes < 1024) {
        return $bytes . ' B';
    }
    $units = [
        'KB',
        'MB',
        'GB',
        'TB'
    ];
    $bytes /= 1024;
    foreach ($units as $unit) {
        if ($bytes < 1024) {
            return number_format($bytes, 1) . ' ' . $unit;
        }
        $bytes /= 1024;
    }
    return number_format($bytes, 1) . ' PB';
}
function formatDate(int $timestamp): string
{
    $difference = time() - $timestamp;
    return match (true) {
        $difference < 60 =>
            'Just now',
        $difference < 3600 =>
            floor($difference / 60) . ' minutes ago',
        $difference < 86400 =>
            floor($difference / 3600) . ' hours ago',
        $difference < 604800 =>
            floor($difference / 86400) . ' days ago',
        default =>
            date('d M Y', $timestamp),
    };
}
function isHidden(string $name): bool
{
    return str_starts_with($name, '.')
        || in_array(
            strtolower($name),
            [
                'thumbs.db',
                'desktop.ini'
            ],
            true
        );
}
/*
|--------------------------------------------------------------------------
| Inline SVG icons
|--------------------------------------------------------------------------
*/
function icon(string $type): string
{
    $icons = [
        'folder' => '
        <svg class="icon folder-icon" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M10 4H2a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h20a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-8z"/>
        </svg>',
        'file' => '
        <svg class="icon" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M6 2h9l5 5v15H6zM14 3v6h6"/>
        </svg>',
        'image' => '
        <svg class="icon image-icon" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3 3.5-4.5 4.5 6H5z"/>
        </svg>',
        'code' => '
        <svg class="icon code-icon" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M9.4 16.6 4.8 12l4.6-4.6L8 6l-6 6 6 6zm5.2-9.2 4.6 4.6-4.6 4.6L16 18l6-6-6-6z"/>
        </svg>',
        'archive' => '
        <svg class="icon archive-icon" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M20 6H4v14h16zM9 2h6v4H9z"/>
        </svg>',
        'markdown' => '
        <svg class="icon markdown-icon" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M3 5h18v14H3zM7 10l2 2 2-2 2 2 2-2"/>
        </svg>',
    ];
    return $icons[$type] ?? $icons['file'];
}
function fileIcon(string $name, bool $directory): string
{
    if ($directory) {
        return icon('folder');
    }
    return match (strtolower(pathinfo($name, PATHINFO_EXTENSION))) {
        'jpg',
        'jpeg',
        'png',
        'gif',
        'webp',
        'svg'
            => icon('image'),
        'php',
        'js',
        'css',
        'json',
        'xml'
            => icon('code'),
        'zip',
        'tar',
        'gz'
            => icon('archive'),
        'md'
            => icon('markdown'),
        default
            => icon('file'),
    };
}
/*
|--------------------------------------------------------------------------
| Directory contents
|--------------------------------------------------------------------------
*/
$items = [];
foreach (scandir($directory) as $item) {
    if ($item === '.') {
        continue;
    }
    if (
        !$config['showHiddenFiles']
        &&
        isHidden($item)
    ) {
        continue;
    }
    $path = $directory . DIRECTORY_SEPARATOR . $item;
    $isDirectory = is_dir($path);
    $items[] = [
        'name'      => $item,
        'directory' => $isDirectory,
        'size'      => $isDirectory ? 0 : filesize($path),
        'modified'  => filemtime($path),
    ];
}
/*
|--------------------------------------------------------------------------
| Initial sort
|--------------------------------------------------------------------------
| Folders first, then alphabetical
*/
usort(
    $items,
    function ($a, $b) {
        if ($a['directory'] !== $b['directory']) {
            return $a['directory'] ? -1 : 1;
        }
        return strcasecmp(
            $a['name'],
            $b['name']
        );
    }
);
$title = basename($directory) ?: 'Home';
/*
|--------------------------------------------------------------------------
| README detection
|--------------------------------------------------------------------------
*/
$readme = null;
if ($config['showReadme']) {
    foreach (
        [
            'README.md',
            'readme.md',
            'Readme.md'
        ] as $file
    ) {
        $candidate =
            $directory
            . DIRECTORY_SEPARATOR
            . $file;
        if (is_file($candidate)) {
            $readme = $candidate;
            break;
        }
    }
}
/*
|--------------------------------------------------------------------------
| Statistics
|--------------------------------------------------------------------------
*/
$totalFiles = 0;
$totalFolders = 0;
$totalSize = 0;
foreach ($items as $item) {
    if ($item['directory']) {
        $totalFolders++;
    } else {
        $totalFiles++;
        $totalSize += $item['size'];
    }
}
/*
|--------------------------------------------------------------------------
| Simple Markdown renderer
|--------------------------------------------------------------------------
*/
function markdown(string $text): string
{
    /*
     * Escape HTML
     */
    $text = htmlspecialchars(
        $text,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
    /*
     * Normalise line endings
     */
    $text = str_replace(
        "\r\n",
        "\n",
        $text
    );
    /*
     * Extract fenced code blocks first
     */
    $codeBlocks = [];
    $text = preg_replace_callback(
        '/```(?:[a-zA-Z0-9_-]+)?\n?(.*?)```/s',
        function ($matches) use (&$codeBlocks) {
            $key = '___CODEBLOCK_' . count($codeBlocks) . '___';
            $codeBlocks[$key] =
                '<pre><code>'
                . trim($matches[1])
                . '</code></pre>';
            return $key;
        },
        $text
    );
    /*
     * Horizontal rules
     */
    $text = preg_replace(
        '/^\s*(---+|\*\*\*+|___+)\s*$/m',
        "\n___HR___\n",
        $text
    );
    /*
     * Headings
     */
    $text = preg_replace(
        '/^### (.+)$/m',
        '<h3>$1</h3>',
        $text
    );
    $text = preg_replace(
        '/^## (.+)$/m',
        '<h2>$1</h2>',
        $text
    );
    $text = preg_replace(
        '/^# (.+)$/m',
        '<h1>$1</h1>',
        $text
    );
    /*
     * Lists
     */
    $lines = explode(
        "\n",
        $text
    );
    $output = [];
    $listStack = [];
    foreach ($lines as $line) {
        /*
         * Top level list item
         */
        if (preg_match('/^- (.+)$/', $line, $match)) {
            while (!empty($listStack) && end($listStack) > 0) {
                $output[] = '</ul>';
                array_pop($listStack);
            }
            if (empty($listStack)) {
                $output[] = '<ul>';
                $listStack[] = 0;
            }
            $output[] = '<li>'
                . $match[1]
                . '</li>';
            continue;
        }
        /*
         * Nested list item
         */
        if (preg_match('/^ {2,}- (.+)$/', $line, $match)) {
            if (empty($listStack)) {
                $output[] = '<ul>';
                $listStack[] = 0;
            }
            if (end($listStack) === 0) {
                $output[] = '<ul>';
                $listStack[] = 1;
            }
            $output[] = '<li>'
                . $match[1]
                . '</li>';
            continue;
        }
        /*
         * Close lists
         */
        while (!empty($listStack)) {
            $output[] = '</ul>';
            array_pop($listStack);
        }
        $output[] = $line;
    }
    while (!empty($listStack)) {
        $output[] = '</ul>';
        array_pop($listStack);
    }
    $text = implode(
        "\n",
        $output
    );
    /*
     * Protect horizontal rules from paragraph handling
     */
    $text = preg_replace(
        '/___HR___/',
        "\n<hr>\n",
        $text
    );
    /*
     * Inline formatting
     */
    $text = preg_replace(
        '/\*\*(.+?)\*\*/',
        '<strong>$1</strong>',
        $text
    );
    $text = preg_replace(
        '/(?<!\*)\*([^*]+)\*(?!\*)/',
        '<em>$1</em>',
        $text
    );
    $text = preg_replace(
        '/`([^`]+)`/',
        '<code>$1</code>',
        $text
    );
    /*
     * Links
     */
    $text = preg_replace(
        '/\[([^\]]+)\]\(([^)]+)\)/',
        '<a href="$2" target="_blank" rel="noopener">$1</a>',
        $text
    );
    /*
     * Paragraph handling
     */
    $blocks = preg_split(
        "/\n\s*\n/",
        $text
    );
    foreach ($blocks as &$block) {
        $block = trim($block);
        if ($block === '') {
            continue;
        }
        /*
         * Existing HTML blocks
         */
        if (
            str_starts_with($block, '<h')
            ||
            str_starts_with($block, '<hr')
            ||
            str_starts_with($block, '<ul')
            ||
            str_starts_with($block, '<pre')
            ||
            str_starts_with($block, '___CODEBLOCK_')
        ) {
            continue;
        }
        /*
         * Normal paragraphs
         */
        $block = nl2br(
            $block
        );
        $block = '<p>'
            . $block
            . '</p>';
    }
    $text = implode(
        "\n",
        $blocks
    );
    /*
     * Restore code blocks
     */
    foreach ($codeBlocks as $key => $html) {
        $text = str_replace(
            $key,
            $html,
            $text
        );
    }
    return $text;
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        <?= htmlspecialchars($title) ?> - DirBrowser
    </title>
</head>
<style>
    :root {
        --bg: #ffffff;
        --text: #24292f;
        --muted: #656d76;
        --border: #d0d7de;
        --hover: #f6f8fa;
        --accent: #0969da;
        --code-bg: #f6f8fa;
    }
    :root.dark {
        --bg: #0d1117;
        --text: #c9d1d9;
        --muted: #8b949e;
        --border: #30363d;
        --hover: #161b22;
        --accent: #58a6ff;
        --code-bg: #161b22;
    }
    * {
        box-sizing: border-box;
    }
    body {
        margin: 0;
        background: var(--bg);
        color: var(--text);
        font-family:
            -apple-system,
            BlinkMacSystemFont,
            "Segoe UI",
            Roboto,
            Helvetica,
            Arial,
            sans-serif;
        font-size: 15px;
    }
    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 2rem;
    }
    h1 {
        display: flex;
        align-items: center;
        gap: .6rem;
        font-size: 1.8rem;
        font-weight: 600;
        margin-bottom: .5rem;
    }
    .path {
        color: var(--muted);
        font-family: monospace;
        font-size: .9rem;
        word-break: break-all;
    }
    .stats {
        color: var(--muted);
        margin: 1rem 0 1.5rem;
    }
    /*
    |--------------------------------------------------------------------------
    | Toolbar
    |--------------------------------------------------------------------------
    */
    .toolbar {
        display: flex;
        align-items: center;
        gap: .75rem;
        margin-bottom: 1.5rem;
    }
    input {
        flex: 1;
        min-width: 0;
        padding: .55rem .75rem;
        border: 1px solid var(--border);
        border-radius: 6px;
        background: var(--bg);
        color: var(--text);
    }
    input:focus {
        outline: 2px solid var(--accent);
        outline-offset: -1px;
    }
    button {
        border: 1px solid var(--border);
        background: var(--bg);
        color: var(--text);
        padding: .55rem .9rem;
        border-radius: 6px;
        cursor: pointer;
    }
    button:hover {
        background: var(--hover);
    }
    .shortcut {
        color: var(--muted);
        font-size: .85rem;
        white-space: nowrap;
    }
    /*
    |--------------------------------------------------------------------------
    | Icons
    |--------------------------------------------------------------------------
    */
    .icon {
        width: 1.25rem;
        height: 1.25rem;
        fill: currentColor;
        flex-shrink: 0;
        vertical-align: middle;
    }
    .folder-icon {
        color: #0969da;
    }
    .image-icon {
        color: #1a7f37;
    }
    .code-icon {
        color: #8250df;
    }
    .archive-icon {
        color: #cf222e;
    }
    .markdown-icon {
        color: #0969da;
    }
    /*
    |--------------------------------------------------------------------------
    | File table
    |--------------------------------------------------------------------------
    */
    table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }
    th:first-child,
    td:first-child {
        width: 75%;
    }
    th:nth-child(2),
    td:nth-child(2) {
        width: 10%;
    }
    th:nth-child(3),
    td:nth-child(3) {
        width: 15%;
    }
    th {
        text-align: left;
        padding: .75rem;
        border-bottom: 2px solid var(--border);
        cursor: pointer;
        user-select: none;
        white-space: nowrap;
    }
    th:hover {
        background: var(--hover);
    }
    .sort-arrow {
        color: var(--accent);
        font-weight: bold;
    }
    td {
        padding: .7rem .75rem;
        border-bottom: 1px solid var(--border);
        vertical-align: middle;
    }
    tr:hover {
        background: var(--hover);
    }
    /*
    |--------------------------------------------------------------------------
    | Filename column
    |--------------------------------------------------------------------------
    */
    .name {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .name a {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .name a:hover {
        color: var(--accent);
    }
    .name-content {
        display: flex;
        align-items: center;
        gap: .6rem;
        min-width: 0;
    }
    .name-content a,
    .name-content span {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    /*
    |--------------------------------------------------------------------------
    | Links
    |--------------------------------------------------------------------------
    */
    a {
        color: inherit;
        text-decoration: none;
    }
    /*
    |--------------------------------------------------------------------------
    | README
    |--------------------------------------------------------------------------
    */
    .readme {
        margin-top: 3rem;
        padding-top: 2rem;
        border-top: 1px solid var(--border);
    }
    .readme h2 {
        display: flex;
        align-items: center;
        gap: .5rem;
        font-size: 1.4rem;
    }
    .readme h1,
    .readme h2,
    .readme h3 {
        margin-top: 1.5rem;
    }
    .readme code {
        background: var(--code-bg);
        padding: .15rem .35rem;
        border-radius: 4px;
    }
    .readme pre {
        background: var(--code-bg);
        padding: 1rem;
        border-radius: 6px;
        overflow-x: auto;
    }
    .readme hr {
        border: 0;
        border-top: 1px solid var(--border);
        margin: 2rem 0;
    }
    .readme ul {
        margin-bottom: 1rem;
        padding-left: 2rem;
    }
    .readme li {
        margin-bottom: .35rem;
    }
    .readme h1 {
        font-size: 2rem;
    }
    .readme h2 {
        font-size: 1.5rem;
    }
    .readme h3 {
        font-size: 1.25rem;
    }
    /*
    |--------------------------------------------------------------------------
    | Footer
    |--------------------------------------------------------------------------
    */
    footer {
        text-align: center;
        color: var(--muted);
        padding: 2rem;
        font-size: .9rem;
    }
    /*
    |--------------------------------------------------------------------------
    | Mobile
    |--------------------------------------------------------------------------
    */
    @media (max-width: 700px) {
        .container {
            padding: 1rem;
        }
        .toolbar {
            flex-wrap: wrap;
        }
        table {
            font-size: .9rem;
        }
        th:nth-child(2),
        td:nth-child(2),
        th:nth-child(3),
        td:nth-child(3) {
            display: none;
        }
        th:first-child,
        td:first-child {
            width: 100%;
        }
    }
</style>
<body>
    <div class="container">
        <h1>
            <?= fileIcon($title, true) ?>
            <?= htmlspecialchars($title) ?>
        </h1>
        <div class="path">
            <?= htmlspecialchars($directory) ?>
        </div>
        <div class="stats">
            <?= $totalFolders ?> folders
            ·
            <?= $totalFiles ?> files
            ·
            <?= formatBytes($totalSize) ?>
        </div>
        <div class="toolbar">
            <input id="search" type="search" placeholder="Ctrl + / Search files...">
            <button id="darkToggle">
                ☾ Dark
            </button>
        </div>
        <table id="fileTable">
            <thead>
                <tr>
                    <th data-sort="name" scope="col">
                        Name <span class="sort-arrow"></span>
                    </th>
                    <th data-sort="size" scope="col">
                        Size <span class="sort-arrow"></span>
                    </th>
                    <th data-sort="modified" scope="col">
                        Modified <span class="sort-arrow"></span>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr data-name="<?= htmlspecialchars(strtolower($item['name'])) ?>" data-size="<?= $item['size'] ?>"
                        data-modified="<?= $item['modified'] ?>" data-type="<?= $item['directory'] ? 'folder' : 'file' ?>">
                        <td class="name" scope="row">
                            <div class="name-content">
                                <?= fileIcon(
                                    $item['name'],
                                    $item['directory']
                                ) ?>
                                <?php if ($item['directory']): ?>
                                    <a href="<?= htmlspecialchars($item['name']) ?>/">
                                        <?= htmlspecialchars($item['name']) ?>
                                    </a>
                                <?php else: ?>
                                    <span>
                                        <?= htmlspecialchars($item['name']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <?= $item['directory'] ? '-' : formatBytes($item['size']) ?>
                        </td>
                        <td>
                            <?= formatDate($item['modified']) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php if ($readme): ?>
            <section class="readme">
                <h2>
                    <?= icon('markdown') ?>
                    README.md
                </h2>
                <?= markdown(file_get_contents($readme)) ?>
            </section>
        <?php endif; ?>
    </div>
    <footer>
        DirBrowser v1.0.0
        &nbsp;·&nbsp;
        Powered by PHP <?= PHP_VERSION ?>
        &nbsp;·&nbsp;
        Running on Laragon
    </footer>
<script>
    /*
    |--------------------------------------------------------------------------
    | Search
    |--------------------------------------------------------------------------
    */
    const search =
        document.getElementById('search');
    const table =
        document.getElementById('fileTable');
    const tbody =
        table.querySelector('tbody');
    search.addEventListener(
        'input',
        function() {
            const value =
                this.value
                .toLowerCase()
                .trim();
            tbody
                .querySelectorAll('tr')
                .forEach(
                    row => {
                        row.style.display =
                            row.dataset.name.includes(value) ?
                            '' :
                            'none';
                    }
                );
        }
    );
    /*
    |--------------------------------------------------------------------------
    | Keyboard shortcuts
    |--------------------------------------------------------------------------
    */
    document.addEventListener(
        'keydown',
        function(event) {
            if (
                event.ctrlKey &&
                event.key === '/'
            ) {
                event.preventDefault();
                search.focus();
            }
            if (
                event.key === 'Escape'
            ) {
                search.value = '';
                search.dispatchEvent(
                    new Event('input')
                );
            }
        }
    );
    /*
    |--------------------------------------------------------------------------
    | Sorting
    |--------------------------------------------------------------------------
    */
    const headers =
        document.querySelectorAll(
            'th[data-sort]'
        );
    let currentSort = 'name';
    let ascending = true;
    function updateSortIndicator() {
        headers.forEach(
            header => {
                const arrow =
                    header.querySelector(
                        '.sort-arrow'
                    );
                arrow.textContent =
                    header.dataset.sort === currentSort ?
                    (ascending ? ' ↑' : ' ↓') :
                    '';
            }
        );
    }
    function sortRows() {
        const rows =
            Array.from(
                tbody.querySelectorAll('tr')
            );
        rows.sort(
            function(a, b) {
                const typeA =
                    a.dataset.type;
                const typeB =
                    b.dataset.type;
                /*
                 * Name sorting keeps folders together
                 */
                if (
                    currentSort === 'name' &&
                    typeA !== typeB
                ) {
                    return ascending ?
                        (
                            typeA === 'folder' ?
                            -1 :
                            1
                        ) :
                        (
                            typeA === 'folder' ?
                            1 :
                            -1
                        );
                }
                let first =
                    a.dataset[currentSort];
                let second =
                    b.dataset[currentSort];
                if (
                    currentSort === 'size' ||
                    currentSort === 'modified'
                ) {
                    first =
                        Number(first);
                    second =
                        Number(second);
                    return ascending ?
                        first - second :
                        second - first;
                }
                return ascending ?
                    first.localeCompare(second) :
                    second.localeCompare(first);
            }
        );
        rows.forEach(
            row =>
            tbody.appendChild(row)
        );
        updateSortIndicator();
    }
    headers.forEach(
        header => {
            header.addEventListener(
                'click',
                function() {
                    const sort =
                        this.dataset.sort;
                    if (
                        currentSort === sort
                    ) {
                        ascending = !ascending;
                    } else {
                        currentSort =
                            sort;
                        ascending =
                            true;
                    }
                    sortRows();
                }
            );
        }
    );
    updateSortIndicator();
    /*
    |--------------------------------------------------------------------------
    | Dark mode
    |--------------------------------------------------------------------------
    */
    const darkToggle =
        document.getElementById(
            'darkToggle'
        );
    if (
        localStorage.getItem(
            'dirbrowser-dark'
        ) === 'true'
    ) {
        document.documentElement.classList.add(
            'dark'
        );
    }
    darkToggle.addEventListener(
        'click',
        function() {
            document.documentElement.classList.toggle(
                'dark'
            );
            localStorage.setItem(
                'dirbrowser-dark',
                document.documentElement.classList.contains(
                    'dark'
                )
            );
        }
    );
</script>
</body>
</html>
