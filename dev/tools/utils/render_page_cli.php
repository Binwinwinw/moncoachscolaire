<?php
// tools/render_page_cli.php
// Helper: render a page via the router in CLI to inspect generated HTML.
$page = $argv[1] ?? 'college/college';
$_GET['page'] = $page;
// include index.php from project root
require_once dirname(__DIR__) . '/index.php';
