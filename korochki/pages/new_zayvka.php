<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: auth.php');
    exit;
}

require_once __DIR__ . '/../helpers/db.php';
$pdo = db_connect();

$errors = [];
$success = false;

$courseId   = '';
$dataNach   = '';
$paymentId  = '';

$kursi   = $pdo->query('SELECT id_kursi, kurs FROM kursi ORDER BY kurs')->fetchAll();
$oplati  = $pdo->query('SELECT id_oplati, oplata FROM oplati ORDER BY oplata')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $courseId  = (int)($_POST['course_id']  ?? 0);
    $dataNach  = trim($_POST['data_nach']   ?? '');
    $paymentId = (int)($_POST['payment_id'] ?? 0);

    if ($courseId <= 0) {
        $errors['course_id'] = 'Выберите курс';
    }
    if ($dataNach === '') {
        $errors['data_nach'] = 'Укажите дату начала обучения';
    }
    if ($paymentId <= 0) {
        $errors['payment_id'] = 'Выберите способ оплаты';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO zayvka (course_id, data_nach, payment_id, status_id, user_id)
                               VALUES (:course_id, :data_nach, :payment_id, 1, :user_id)");
        $stmt->execute([
            ':course_id'  => $courseId,
            ':data_nach'  => $dataNach,
            ':payment_id' => $paymentId,
            ':user_id'    => $_SESSION['user_id'],
        ]);

        $success = true;
        $courseId = $dataNach = $paymentId = '';
    }
}

$title       = 'Корочки.есть — Подача заявки';
$page_title  = 'Подача заявки';
$breadcrumbs = ['Главная' => 'index.php', 'Просмотр заявок' => 'zayvki.php', 'Подача заявки' => ''];
$nav_buttons = [
    ['title' => 'Мои заявки', 'url' => 'zayvki.php',  'active' => false],
    ['title' => 'Выход',      'url' => 'logout.php',  'active' => false],
    ['title' => 'Главная',    'url' => 'index.php',   'active' => false],
];

ob_start();
?>

<section class="new-request-page">
    <div class="form-wrapper">

        <?php if ($success): ?>
            <div class="success-message">
                <p>✅ Заявка успешно создана!</p>
                <p><a href="zayvki.php">Просмотреть заявки</a></p>
            </div>
        <?php else: ?>

        <form class="auth-form" method="POST" action="new_zayvka.php" novalidate>
            <div class="form-group">
                <label for="course_id">Наименование курса</label>
                <select id="course_id" name="course_id"
                        class="<?= isset($errors['course_id']) ? 'is-invalid' : '' ?>" required>
                    <option value="">-- Выберите курс --</option>
                    <?php foreach ($kursi as $k): ?>
                        <option value="<?= (int)$k['id_kursi'] ?>"
                            <?= (int)$courseId === (int)$k['id_kursi'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($k['kurs']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (!empty($errors['course_id'])): ?>
                    <span class="error-text"><?= htmlspecialchars($errors['course_id']) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="data_nach">Желаемая дата начала обучения</label>
                <input type="date" id="data_nach" name="data_nach"
                       value="<?= htmlspecialchars($dataNach) ?>"
                       class="<?= isset($errors['data_nach']) ? 'is-invalid' : '' ?>" required>
                <?php if (!empty($errors['data_nach'])): ?>
                    <span class="error-text"><?= htmlspecialchars($errors['data_nach']) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="payment_id">Способ оплаты</label>
                <select id="payment_id" name="payment_id"
                        class="<?= isset($errors['payment_id']) ? 'is-invalid' : '' ?>" required>
                    <option value="">-- Выберите способ оплаты --</option>
                    <?php foreach ($oplati as $o): ?>
                        <option value="<?= (int)$o['id_oplati'] ?>"
                            <?= (int)$paymentId === (int)$o['id_oplati'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($o['oplata']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (!empty($errors['payment_id'])): ?>
                    <span class="error-text"><?= htmlspecialchars($errors['payment_id']) ?></span>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Подать заявку</button>

            <p class="auth-hint">
                Заявка уже создана?<br>
                <a href="zayvki.php">Просмотреть</a>
            </p>
        </form>
        <?php endif; ?>
    </div>
</section>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';