<?php
// Start the session before any HTML is sent to the browser.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$scriptPath = $_SERVER['SCRIPT_NAME'] ?? '';
if (strpos($scriptPath, '/candidate/') !== false || strpos($scriptPath, '/admin/') !== false) {
    $basePath = '../';
} else {
    $basePath = '';
}

$pageTitle = $pageTitle ?? 'Career Grow Infotech Job Portal';
$currentPage = basename($scriptPath ?: 'index.php');
$siteStylesheetVersion = (string) filemtime(__DIR__ . '/../assets/css/style.css');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Career Grow Infotech Job Portal - connecting candidates with career opportunities.">
    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="icon" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>assets/images/logo.webp" type="image/webp">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>assets/css/style.css?v=<?php echo htmlspecialchars($siteStylesheetVersion, ENT_QUOTES, 'UTF-8'); ?>">
    <?php if (strpos($scriptPath, '/admin/') === false): ?>
    <script>
        // Public/Candidate theme initialization to prevent FOUC.
        // Uses localStorage key 'site-theme'. Values: 'light' or 'dark'.
        (function(){
            try {
                var t = localStorage.getItem('site-theme');
                if (t === 'dark') {
                    document.documentElement.setAttribute('data-theme', 'dark');
                } else {
                    document.documentElement.removeAttribute('data-theme');
                }
            } catch (e) {
                // silent fallback
            }
        })();
    </script>
    <?php endif; ?>
</head>
<body class="d-flex flex-column min-vh-100">
<?php if (strpos($scriptPath, '/admin/') !== false): ?>
    <div class="admin-layout" aria-hidden="false">
<?php endif; ?>
