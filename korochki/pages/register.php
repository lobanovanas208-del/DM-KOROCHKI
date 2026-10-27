<?php
session_start();

if (!empty($_SESSION['user_id'])) {
    header('Location: zayvki.php');
    exit;
}

require_once __DIR__ . '/../helpers/db.php';

$errors  = [];
$success = false;

$login = $fio = $email = $phone = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login    = trim($_POST['login']    ?? '');
    $password = $_POST['password']      ?? '';
    $fio      = trim($_POST['fio']      ?? '');
    $email    = trim($_POST['email']    ?? '');
    $phone    = trim($_POST['phone']    ?? '');

    // Валидация логина
    if ($login === '') {
        $errors['login'] = 'Введите логин';
    } elseif (strlen($login) < 6) {
        $errors['login'] = 'минимум 6 символов';
    } elseif (!preg_match('/^[a-zA-Z0-9]+$/', $login)) {
        $errors['login'] = 'латиница и цифры';
    }

    // Валидация пароля
    if ($password === '') {
        $errors['password'] = 'Введите пароль';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'минимум 8 символов';
    }

    // Валидация ФИО
    if ($fio === '') {
        $errors['fio'] = 'Введите ФИО';
    } elseif (!preg_match('/^[а-яА-ЯёЁ\s]+$/u', $fio)) {
        $errors['fio'] = 'только кириллица и пробелы';
    }

    // Валидация email
    if ($email === '') {
        $errors['email'] = 'Введите email';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Неверный email';
    }

    // Валидация телефона (с маской 8(XXX)XXX-XX-XX)
    if ($phone === '') {
        $errors['phone'] = 'Введите телефон';
    } elseif (!preg_match('/^8\(\d{3}\)\d{3}-\d{2}-\d{2}$/', $phone)) {
        $errors['phone'] = 'Формат: 8(XXX)XXX-XX-XX';
    }

    if (empty($errors)) {
        $pdo = db_connect();

        // Проверка уникальности логина
        $stmt = $pdo->prepare('SELECT id_polzovateli FROM polzovateli WHERE login = :login LIMIT 1');
        $stmt->execute([':login' => $login]);
        if ($stmt->fetch()) {
            $errors['login'] = 'Такой логин уже занят';
        }

        // Проверка уникальности email
        if (empty($errors)) {
            $stmt = $pdo->prepare('SELECT id_polzovateli FROM polzovateli WHERE email = :email LIMIT 1');
            $stmt->execute([':email' => $email]);
            if ($stmt->fetch()) {
                $errors['email'] = 'Такой email уже зарегистрирован';
            }
        }

        if (empty($errors)) {
            $passwordHash = md5($password);
            $stmt = $pdo->prepare("INSERT INTO polzovateli (login, password, fio, phone, email, role_id)
                                   VALUES (:login, :password, :fio, :phone, :email, 1)");
            $stmt->execute([
                ':login'    => $login,
                ':password' => $passwordHash,
                ':fio'      => $fio,
                ':phone'    => $phone,
                ':email'    => $email,
            ]);

            $success = true;
            $login = $fio = $email = $phone = '';
        }
    }
}

$title       = 'Корочки.есть — Регистрация';
$page_title  = '';
$breadcrumbs = ['Главная' => 'index.php', 'Регистрация' => ''];
$nav_buttons = [
    ['title' => 'Вход',    'url' => 'auth.php',    'active' => false],
    ['title' => 'Главная', 'url' => 'index.php',   'active' => false],
];

ob_start();
?>

<section class="auth-page">
    <div class="auth-wrapper">
        <h2 class="auth-title">Регистрация</h2>

        <?php if ($success): ?>
            <div class="success-message">
                <p>✅ Регистрация прошла успешно!</p>
                <p><a href="auth.php">Войти в систему</a></p>
            </div>
        <?php else: ?>

        <form class="auth-form" method="POST" action="register.php" novalidate>

            <!-- ЛОГИН -->
            <div class="form-group">
                <label for="login">Логин</label>
                <input type="text" id="login" name="login"
                       value="<?= htmlspecialchars($login) ?>"
                       placeholder="Латиница и цифры, ≥6"
                       class="<?= isset($errors['login']) ? 'is-invalid' : '' ?>"
                       autocomplete="username" required>
                <?php if (!empty($errors['login'])): ?>
                    <span class="error-text"><?= htmlspecialchars($errors['login']) ?></span>
                <?php endif; ?>
            </div>

            <!-- ПАРОЛЬ -->
            <div class="form-group">
                <label for="password">Пароль</label>
                <input type="password" id="password" name="password"
                       placeholder="Минимум 8 символов"
                       class="<?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                       autocomplete="new-password" required>
                <?php if (!empty($errors['password'])): ?>
                    <span class="error-text"><?= htmlspecialchars($errors['password']) ?></span>
                <?php endif; ?>
            </div>

            <!-- ФИО -->
            <div class="form-group">
                <label for="fio">ФИО</label>
                <input type="text" id="fio" name="fio"
                       value="<?= htmlspecialchars($fio) ?>"
                       placeholder="Иванов Иван Иванович"
                       class="<?= isset($errors['fio']) ? 'is-invalid' : '' ?>"
                       autocomplete="name" required>
                <?php if (!empty($errors['fio'])): ?>
                    <span class="error-text"><?= htmlspecialchars($errors['fio']) ?></span>
                <?php endif; ?>
            </div>

            <!-- EMAIL -->
            <div class="form-group">
                <label for="email">Адрес электронной почты</label>
                <input type="email" id="email" name="email"
                       value="<?= htmlspecialchars($email) ?>"
                       placeholder="example@mail.ru"
                       class="<?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                       autocomplete="email" required>
                <?php if (!empty($errors['email'])): ?>
                    <span class="error-text"><?= htmlspecialchars($errors['email']) ?></span>
                <?php endif; ?>
            </div>

            <!-- ТЕЛЕФОН С МАСКОЙ -->
            <div class="form-group">
                <label for="phone">Телефон</label>
                <input type="tel" id="phone" name="phone"
                       value="<?= htmlspecialchars($phone) ?>"
                       placeholder="8(XXX)XXX-XX-XX"
                       class="<?= isset($errors['phone']) ? 'is-invalid' : '' ?>"
                       maxlength="18"
                       autocomplete="tel" required>
                <?php if (!empty($errors['phone'])): ?>
                    <span class="error-text"><?= htmlspecialchars($errors['phone']) ?></span>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Зарегистрироваться</button>

            <p class="auth-hint">
                Уже есть аккаунт?<br>
                <a href="auth.php">Войти</a>
            </p>
        </form>
        <?php endif; ?>
    </div>
</section>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';