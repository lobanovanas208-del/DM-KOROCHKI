<?php
session_start();

if (!empty($_SESSION['user_id'])) {
    header('Location: zayvki.php');
    exit;
}

require_once __DIR__ . '/../helpers/db.php';

$errors = [];
$login  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login    = trim($_POST['login']    ?? '');
    $password = $_POST['password']      ?? '';

    if ($login === '' || $password === '') {
        $errors[] = 'Заполните все поля.';
    } else {
        $pdo = db_connect();

        $sql = "SELECT p.id_polzovateli, p.login, p.password, p.fio, p.role_id, r.rol
                FROM polzovateli p
                LEFT JOIN roli r ON r.id_roli = p.role_id
                WHERE p.login = :login LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':login' => $login]);
        $user = $stmt->fetch();

        if (!$user) {
            $errors['login'] = 'неверный логин';
        } elseif ($user['password'] !== md5($password)) {
            $errors['password'] = 'неверный пароль';
        } else {
            $_SESSION['user_id']   = (int)$user['id_polzovateli'];
            $_SESSION['user_login']= $user['login'];
            $_SESSION['user_fio']  = $user['fio'];
            $_SESSION['role_id']   = (int)$user['role_id'];
            $_SESSION['role_name'] = $user['rol'] ?? 'пользователь';

            if ((int)$user['role_id'] === 2) {
                header('Location: admin.php');
            } else {
                header('Location: zayvki.php');
            }
            exit;
        }
    }
}

$title       = 'Корочки.есть — Авторизация';
$page_title  = '';
$breadcrumbs = ['Главная' => 'index.php', 'Авторизация' => ''];
$nav_buttons = [
    ['title' => 'Регистрация', 'url' => 'register.php', 'active' => false],
    ['title' => 'Главная',     'url' => 'index.php',    'active' => false],
];

ob_start();
?>

<section class="auth-page">
    <div class="auth-wrapper">
        <h2 class="auth-title">Авторизация</h2>

        <form class="auth-form" method="POST" action="auth.php" novalidate>
            <div class="form-group">
                <label for="login">Логин</label>
                <input type="text" id="login" name="login"
                       value="<?= htmlspecialchars($login) ?>"
                       placeholder="Введите логин"
                       class="<?= isset($errors['login']) ? 'is-invalid' : '' ?>" required>
                <?php if (!empty($errors['login'])): ?>
                    <span class="error-text"><?= htmlspecialchars($errors['login']) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="password">Пароль</label>
                <input type="password" id="password" name="password"
                       placeholder="Введите пароль"
                       class="<?= isset($errors['password']) ? 'is-invalid' : '' ?>" required>
                <?php if (!empty($errors['password'])): ?>
                    <span class="error-text"><?= htmlspecialchars($errors['password']) ?></span>
                <?php endif; ?>
            </div>

            <?php if (!empty($errors) && !isset($errors['login']) && !isset($errors['password'])): ?>
                <div class="error-text">
                    <?php foreach ($errors as $err): ?>
                        <p><?= htmlspecialchars($err) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <button type="submit" class="btn btn-primary btn-block">Войти</button>

            <p class="auth-hint">
                Еще не зарегистрированы?<br>
                <a href="register.php">Регистрация</a>
            </p>
        </form>
    </div>
</section>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';