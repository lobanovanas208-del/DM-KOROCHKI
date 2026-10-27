<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: auth.php');
    exit;
}

require_once __DIR__ . '/../helpers/db.php';
$pdo = db_connect();

// ---------- Обработка отзыва ----------
$reviewSuccess = false;
$reviewError   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $reviewId  = (int)($_POST['review_id']  ?? 0);
    $comment   = trim($_POST['comment']     ?? '');
    $stars     = (int)($_POST['stars']      ?? 0);

    if ($stars >= 1 && $stars <= 5) {
        if ($reviewId > 0) {
            $stmt = $pdo->prepare("UPDATE otziv SET coment = :coment, zvezda = :zvezda WHERE id_otziv = :id");
            $stmt->execute([':coment' => $comment, ':zvezda' => $stars, ':id' => $reviewId]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO otziv (coment, zvezda) VALUES (:coment, :zvezda)");
            $stmt->execute([':coment' => $comment, ':zvezda' => $stars]);
            $reviewId = (int)$pdo->lastInsertId();
        }

        $stmt = $pdo->prepare("UPDATE zayvka SET review_id = :review_id WHERE id_request = :id AND user_id = :user_id");
        $stmt->execute([':review_id' => $reviewId, ':id' => (int)$_POST['request_id'], ':user_id' => $_SESSION['user_id']]);

        $reviewSuccess = true;
    } else {
        $reviewError = 'Оценка должна быть от 1 до 5';
    }
}

// ---------- Фильтр и пагинация ----------
$perPage  = 3;
$page     = max(1, (int)($_GET['page'] ?? 1));
$offset   = ($page - 1) * $perPage;
$filterStatus = (int)($_GET['status'] ?? 0);

$where = "WHERE z.user_id = :user_id";
$params = [':user_id' => $_SESSION['user_id']];

if ($filterStatus > 0) {
    $where .= " AND z.status_id = :status_id";
    $params[':status_id'] = $filterStatus;
}

// Счётчик
$countSql = "SELECT COUNT(*) AS cnt FROM zayvka z $where";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$total = (int)$countStmt->fetch()['cnt'];
$totalPages = max(1, (int)ceil($total / $perPage));
if ($page > $totalPages) $page = $totalPages;

// Данные
$sql = "SELECT z.id_request, k.kurs AS course_name, s.status AS status_name,
               s.id_statusi AS status_id,
               o.oplata AS payment_name, z.data_nach, z.review_id,
               ot.coment AS review_text, ot.zvezda AS review_stars
        FROM zayvka z
        LEFT JOIN kursi k ON z.course_id = k.id_kursi
        LEFT JOIN statusi s ON z.status_id = s.id_statusi
        LEFT JOIN oplati o ON z.payment_id = o.id_oplati
        LEFT JOIN otziv ot ON z.review_id = ot.id_otziv
        $where
        ORDER BY z.id_request DESC
        LIMIT $perPage OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$requests = $stmt->fetchAll();

// Статусы для фильтра
$statusi = $pdo->query('SELECT id_statusi, status FROM statusi ORDER BY id_statusi')->fetchAll();

$title       = 'Корочки.есть — Мои заявки';
$page_title  = 'Просмотр заявок';
$breadcrumbs = ['Главная' => 'index.php', 'Просмотр заявок' => ''];
$nav_buttons = [
    ['title' => 'Выход',   'url' => 'logout.php', 'active' => false],
    ['title' => 'Главная', 'url' => 'index.php',  'active' => false],
];

ob_start();
?>

<section class="requests-page">

    <div class="requests-header">
        <a href="new_zayvka.php" class="btn btn-primary">Создать заявку</a>
    </div>

    <?php if ($reviewSuccess): ?>
        <div class="success-message">✅ Отзыв сохранён!</div>
    <?php endif; ?>
    <?php if ($reviewError): ?>
        <div class="error-message">❌ <?= htmlspecialchars($reviewError) ?></div>
    <?php endif; ?>

    <!-- Фильтр -->
    <div class="filter-bar">
        <form method="GET" action="zayvki.php" class="filter-form">
            <label for="statusFilter">Фильтр по статусу:</label>
            <select name="status" id="statusFilter" onchange="this.form.submit()">
                <option value="0" <?= $filterStatus === 0 ? 'selected' : '' ?>>Все статусы</option>
                <?php foreach ($statusi as $st): ?>
                    <option value="<?= (int)$st['id_statusi'] ?>" <?= $filterStatus === (int)$st['id_statusi'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($st['status']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <?php if (empty($requests)): ?>
        <div class="empty-state">
            <p>Заявок не найдено</p>
            <a href="new_zayvka.php" class="link-primary">Создать первую</a>
        </div>
    <?php else: ?>
        <div class="requests-grid">
            <?php foreach ($requests as $req): ?>
                <div class="request-card">
                    <div class="request-card-header">
                        <h3>Заявка №<?= (int)$req['id_request'] ?></h3>
                        <span class="status-badge status-user-<?= (int)$req['status_id'] ?>">
                            <?= htmlspecialchars($req['status_name'] ?? '—') ?>
                        </span>
                    </div>
                    <div class="request-card-body">
                        <div class="request-row">
                            <span class="label">Курс:</span>
                            <span class="value"><?= htmlspecialchars($req['course_name'] ?? 'Не указан') ?></span>
                        </div>
                        <div class="request-row">
                            <span class="label">Оплата:</span>
                            <span class="value"><?= htmlspecialchars($req['payment_name'] ?? 'Не указан') ?></span>
                        </div>
                        <div class="request-row">
                            <span class="label">Дата начала:</span>
                            <span class="value"><?= htmlspecialchars($req['data_nach'] ?? 'Не указана') ?></span>
                        </div>
                        <?php if ((int)$req['status_id'] === 3): ?>
                            <div class="request-row">
                                <span class="label">Отзыв:</span>
                                <?php if ($req['review_id']): ?>
                                    <span class="value"><?= htmlspecialchars($req['review_text'] ?? '') ?> (<?= (int)$req['review_stars'] ?> ⭐)</span>
                                <?php else: ?>
                                    <span class="value text-muted">Не оставлен</span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="request-card-footer">
                        <?php if ((int)$req['status_id'] === 3): ?>
                            <button type="button" class="btn-review"
                                    onclick="openReviewModal(<?= (int)$req['id_request'] ?>, <?= (int)($req['review_id'] ?? 0) ?>, '<?= addslashes($req['review_text'] ?? '') ?>', <?= (int)($req['review_stars'] ?? 5) ?>)">
                                <?= $req['review_id'] ? 'Изменить отзыв' : 'Оставить отзыв' ?>
                            </button>
                        <?php else: ?>
                            <span class="text-muted">Отзыв после завершения обучения</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
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

<!-- Модальное окно отзыва -->
<div id="reviewModal" class="modal-overlay" style="display:none;">
    <div class="modal-content">
        <h3>Оставить отзыв</h3>
        <form method="POST" action="zayvki.php">
            <input type="hidden" name="request_id" id="reviewRequestId" value="">
            <input type="hidden" name="review_id"  id="reviewId" value="">

            <div class="form-group">
                <label>Оценка (1-5):</label>
                <input type="number" name="stars" id="reviewStars"
                       min="1" max="5" step="1" value="5" required
                       style="width: 80px; padding: 8px 12px; font-size: 16px; text-align: center;">
            </div>

            <div class="form-group">
                <label>Комментарий:</label>
                <textarea name="comment" id="reviewComment" rows="4" placeholder="Ваш отзыв..."></textarea>
            </div>

            <div class="modal-buttons">
                <button type="submit" name="submit_review" class="btn btn-primary">Сохранить</button>
                <button type="button" class="btn" onclick="closeReviewModal()">Отмена</button>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';