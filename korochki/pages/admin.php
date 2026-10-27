<?php
/**
 * admin.php — панель администратора
 * Доступ только для role_id = 2
 * Просмотр всех заявок, смена статусов, просмотр отзывов
 * С фильтром по статусу и пагинацией
 */
session_start();

// Проверка прав доступа
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] !== 2) {
    header('Location: auth.php');
    exit;
}

require_once __DIR__ . '/../helpers/db.php';
$pdo = db_connect();

$successMsg = '';
$errorMsg   = '';

// ---------- Обработка смены статуса ----------
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

// ---------- Фильтр и пагинация ----------
$perPage      = 5;
$page         = max(1, (int)($_GET['page'] ?? 1));
$offset       = ($page - 1) * $perPage;
$filterStatus = (int)($_GET['status'] ?? 0); // 0 = все статусы

$where  = "";
$params = [];

if ($filterStatus > 0) {
    $where = "WHERE z.status_id = :status_id";
    $params[':status_id'] = $filterStatus;
}

// Счётчик записей
$countSql  = "SELECT COUNT(*) AS cnt FROM zayvka z $where";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$total      = (int)$countStmt->fetch()['cnt'];
$totalPages = max(1, (int)ceil($total / $perPage));
if ($page > $totalPages) $page = $totalPages;

// ---------- Получение заявок ----------
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
        $where
        ORDER BY z.id_request DESC
        LIMIT $perPage OFFSET $offset";

$stmt     = $pdo->prepare($sql);
$stmt->execute($params);
$requests = $stmt->fetchAll();

// Получаем все статусы для фильтра и селекта
$statusi = $pdo->query('SELECT id_statusi, status FROM statusi ORDER BY id_statusi')->fetchAll();

// ---------- Данные для layout.php ----------
$title       = 'Корочки.есть — Админ-панель';
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
        <div class="error-message"> <?= htmlspecialchars($errorMsg) ?></div>
    <?php endif; ?>

    <!-- Информация об администраторе -->
    <div class="admin-info">
        <p>Администратор: <strong><?= htmlspecialchars($_SESSION['user_fio']) ?></strong>
           (<?= htmlspecialchars($_SESSION['user_login']) ?>)</p>
        <p>Всего заявок: <strong><?= $total ?></strong></p>
    </div>

    <!-- Фильтр по статусу -->
    <div class="filter-bar">
        <form method="GET" action="admin.php" class="filter-form">
            <label for="statusFilter">Фильтр по статусу:</label>
            <select name="status" id="statusFilter" onchange="this.form.submit()">
                <option value="0" <?= $filterStatus === 0 ? 'selected' : '' ?>>Все статусы</option>
                <?php foreach ($statusi as $st): ?>
                    <option value="<?= (int)$st['id_statusi'] ?>"
                        <?= $filterStatus === (int)$st['id_statusi'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($st['status']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <?php if (empty($requests)): ?>
        <div class="empty-state">
            <p>Заявок не найдено</p>
        </div>
    <?php else: ?>
        <div class="admin-table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>№</th>
                        <th>Пользователь</th>
                        <th>Курс</th>
                        <th>Дата</th>
                        <th>Оплата</th>
                        <th>Отзыв</th>
                        <th>Статус</th>
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
                                    <span class="text-muted">—</span>
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

        <!-- Пагинация -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>&status=<?= $filterStatus ?>" class="page-link">&lt;</a>
                <?php else: ?>
                    <span class="page-link disabled">&lt;</span>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?= $i ?>&status=<?= $filterStatus ?>"
                       class="page-link <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= $page + 1 ?>&status=<?= $filterStatus ?>" class="page-link">&gt;</a>
                <?php else: ?>
                    <span class="page-link disabled">&gt;</span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

</section>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';