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

        <!-- Слайдер с 4 изображениями, автопрокрутка 3 сек, только точки -->
        <div class="slider" id="mainSlider">
            <div class="slider-track" id="sliderTrack">
                <img src="/DM-KOROCHKI/korochki/media/image01.webp" alt="Слайд 1">
                <img src="/DM-KOROCHKI/korochki/media/image17.jpeg"  alt="Слайд 2">
                <img src="/DM-KOROCHKI/korochki/media/image16.jpg"  alt="Слайд 3">
                <img src="/DM-KOROCHKI/korochki/media/image05.jpg"  alt="Слайд 4">
            </div>

            <!-- Только точки-индикаторы (без стрелок) -->
            <div class="slider-dots" id="sliderDots">
                <span class="dot active" data-slide="0"></span>
                <span class="dot" data-slide="1"></span>
                <span class="dot" data-slide="2"></span>
                <span class="dot" data-slide="3"></span>
            </div>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';