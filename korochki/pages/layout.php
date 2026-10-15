<?php
if (!isset($title))       $title       = 'Корочки.есть';
if (!isset($page_title))  $page_title  = '';
if (!isset($breadcrumbs)) $breadcrumbs = [];
if (!isset($nav_buttons)) $nav_buttons = [];
if (!isset($content))     $content     = '';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="stylesheet" href="/DM-KOROCHKI/korochki/styles/style.css">
</head>
<body>

<header class="site-header">
    <div class="header-inner">
        <a href="/DM-KOROCHKI/korochki/pages/index.php" class="logo">
            <img src="/DM-KOROCHKI/korochki/media/image02.jpg" alt="Логотип">
        </a>
        <h1 class="site-title">Корочки.есть</h1>
        <nav class="header-nav">
            <?php foreach ($nav_buttons as $btn): ?>
                <a href="<?= htmlspecialchars($btn['url']) ?>"
                   class="nav-btn <?= !empty($btn['active']) ? 'active' : '' ?>">
                    <?= htmlspecialchars($btn['title']) ?>
                </a>
            <?php endforeach; ?>
        </nav>
    </div>
</header>

<?php if (!empty($breadcrumbs)): ?>
    <nav class="breadcrumbs">
        <?php
        $i = 0;
        $total = count($breadcrumbs);
        foreach ($breadcrumbs as $label => $url):
            $i++;
            $isLast = ($i === $total);
        ?>
            <?php if (!$isLast && $url !== ''): ?>
                <a href="<?= htmlspecialchars($url) ?>"><?= htmlspecialchars($label) ?></a>
            <?php else: ?>
                <span class="current"><?= htmlspecialchars($label) ?></span>
            <?php endif; ?>
            <?php if (!$isLast): ?> / <?php endif; ?>
        <?php endforeach; ?>
    </nav>
<?php endif; ?>

<?php if ($page_title !== ''): ?>
    <section class="page-title-bar">
        <h2><?= htmlspecialchars($page_title) ?></h2>
    </section>
<?php endif; ?>

<main class="site-main">
    <?= $content ?>
</main>

<footer class="site-footer">
    <div class="footer-inner">
        <p>&copy; <?= date('Y') ?> Портал «Корочки.есть» — онлайн-курсы дополнительного профессионального образования.</p>
    </div>
</footer>

<script src="/DM-KOROCHKI/korochki/scripts/script.js"></script>
</body>
</html>