<?php
/**
 * admin.php — панель администратора
 */
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] !== 2) {
    header('Location: auth.php');
    exit;
}

require_once __DIR__ . '/../helpers/db.php';
$pdo = db_connect();

$successMsg = '';
$errorMsg   = '';

// Обработка смены статуса
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requestId = (int)($_POST['request_id'] ?? 0);
    $statusId  = (int)($_POST['status_id']  ?? 0);

    if ($requestId > 0 && $statusId > 0) {
        $stmt = $pdo->prepare("UPDATE zayvka SET status_id = :status_id WHERE id_request = :id");
        $stmt->execute([':status_id' => $statusId, ':id' => $requestId]);
        $successMsg = "Статус заявки №{$requestId} изменён.";
    } else {
        $errorMsg = 'Некорректные данные.';
    }
}

// ✅ ИСПРАВЛЕНО: добавлено z.review_id в SELECT
$sql = "SELECT 
            z.id_request,
            z.review_id,
            p.fio AS user_fio,
            p.login AS user_login,
            k.kurs AS course_name,
            s.status AS status_name,
            s.id_statusi AS status_id,
            o.oplata AS payment_name,
            z.data_nach,
            ot.coment AS review_text,
            ot.zvezda AS review_stars
        FROM zayvka z
        LEFT JOIN polzovateli p ON z.user_id = p.id_polzovateli
        LEFT JOIN kursi k ON z.course_id = k.id_kursi
        LEFT JOIN statusi s ON z.status_id = s.id_statusi
        LEFT JOIN oplati o ON z.payment_id = o.id_oplati
        LEFT JOIN otziv ot ON z.review_id = ot.id_otziv
        ORDER BY z.id_request DESC";

$requests = $pdo->query($sql)->fetchAll();
$statusi = $pdo->query('SELECT id_statusi, status FROM statusi ORDER BY id_statusi')->fetchAll();

$title       = 'Корочки.есть — Панель администратора';
$page_title  = 'Управление заявками';
$breadcrumbs = ['Главная' => 'index.php', 'Админ-панель' => ''];
$nav_buttons = [
    ['title' => 'Главная', 'url' => 'index.php',  'active' => false],
    ['title' => 'Выход',   'url' => 'logout.php', 'active' => false],
];

ob_start();
?>

<section class="admin-page">

    <?php if ($successMsg): ?>
        <div class="success-message">✅ <?= htmlspecialchars($successMsg) ?></div>
    <?php endif; ?>
    <?php if ($errorMsg): ?>
        <div class="error-message">❌ <?= htmlspecialchars($errorMsg) ?></div>
    <?php endif; ?>

    <div class="admin-info">
        <p>Администратор: <strong><?= htmlspecialchars($_SESSION['user_fio']) ?></strong>
           (<?= htmlspecialchars($_SESSION['user_login']) ?>)</p>
        <p>Всего заявок: <strong><?= count($requests) ?></strong></p>
    </div>

    <?php if (empty($requests)): ?>
        <div class="empty-state">
            <p>Заявок пока нет.</p>
        </div>
    <?php else: ?>
        <div class="admin-table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>№</th>
                        <th>Пользователь</th>
                        <th>Курс</th>
                        <th>Дата начала</th>
                        <th>Оплата</th>
                        <th>Статус</th>
                        <th>Отзыв</th>
                        <th>Действие</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requests as $req): ?>
                        <tr>
                            <td><?= (int)$req['id_request'] ?></td>
                            <td>
                                <?= htmlspecialchars($req['user_fio'] ?? '—') ?><br>
                                <small class="text-muted"><?= htmlspecialchars($req['user_login'] ?? '') ?></small>
                            </td>
                            <td><?= htmlspecialchars($req['course_name'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($req['data_nach'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($req['payment_name'] ?? '—') ?></td>
                            <td>
                                <span class="status-badge status-<?= (int)$req['status_id'] ?>">
                                    <?= htmlspecialchars($req['status_name'] ?? '—') ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($req['review_id'])): ?>
                                    <div class="review-cell">
                                        <div class="review-stars">
                                            <?php
                                            $stars = (int)($req['review_stars'] ?? 0);
                                            for ($i = 1; $i <= 5; $i++):
                                            ?>
                                                <span class="star <?= $i <= $stars ? 'filled' : '' ?>">★</span>
                                            <?php endfor; ?>
                                        </div>
                                        <div class="review-text">
                                            <?= htmlspecialchars($req['review_text'] ?? 'Без комментария') ?>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">Отзыв не оставлен</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="POST" action="admin.php" class="status-form">
                                    <input type="hidden" name="request_id" value="<?= (int)$req['id_request'] ?>">
                                    <select name="status_id">
                                        <?php foreach ($statusi as $st): ?>
                                            <option value="<?= (int)$st['id_statusi'] ?>"
                                                <?= (int)$req['status_id'] === (int)$st['id_statusi'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($st['status']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn-sm">OK</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

</section>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';