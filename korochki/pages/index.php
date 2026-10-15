<?php
session_start();

$title       = 'Корочки.есть — Главная';
$page_title  = '';
$breadcrumbs = ['Главная' => ''];

if (!empty($_SESSION['user_id'])) {
    $nav_buttons = [
        ['title' => 'Мои заявки', 'url' => 'zayvki.php',  'active' => false],
        ['title' => 'Выход',      'url' => 'logout.php',  'active' => false],
    ];
} else {
    $nav_buttons = [
        ['title' => 'Регистрация', 'url' => 'register.php', 'active' => false],
        ['title' => 'Вход',        'url' => 'auth.php',     'active' => false],
    ];
}

ob_start();
?>

<section class="hero">
    <div class="hero-inner">
        <h2 class="hero-title">Портал «Корочки.есть»</h2>
        <p class="hero-text">
            Портал «Корочки.есть» представляет собой информационную систему
            для записи на онлайн курсы дополнительного профессионального образования.
        </p>

        <div class="slider">
            <div class="slider-track">
                <img src="/DM-KOROCHKI/korochki/media/image01.webp" alt="Слайд 1">
                <img src="/DM-KOROCHKI/korochki/media/image03.png" alt="Слайд 2">
                <img src="/DM-KOROCHKI/korochki/media/image04.jpg" alt="Слайд 3">
            </div>
            <div class="slider-dots">
                <span class="dot active"></span>
                <span class="dot"></span>
                <span class="dot"></span>
            </div>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';