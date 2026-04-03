<?php
// includes/header.php
// Head HTML centralisé pour MonCoachScolaire
if (defined('HEAD_EMITTED')) {
    return;
}
define('HEAD_EMITTED', true);
$page_title = $page_title ?? 'MonCoachScolaire';
$page_css = $page_css ?? 'dashboard.css';
$cssStyle = function_exists('asset_url') ? asset_url('assets/css/style.css') : '/public/assets/css/style.css';
$cssPage = function_exists('asset_url') ? asset_url('assets/css/pages/' . $page_css) : '/public/assets/css/pages/' . htmlspecialchars($page_css, ENT_QUOTES);
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="stylesheet" href="<?php echo htmlspecialchars($cssStyle, ENT_QUOTES); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars($cssPage, ENT_QUOTES); ?>">
    <?php
    // Exposer baseUrl pour JavaScript
    if (function_exists('detectBaseUrl')) {
        $jsBaseUrl = detectBaseUrl();
    } else {
        $jsBaseUrl = isset($baseUrl) ? $baseUrl : '';
    }
echo "<script>window.baseUrl = " . json_encode($jsBaseUrl, JSON_UNESCAPED_SLASHES) . ";</script>\n";
?>
</head>
<body>
