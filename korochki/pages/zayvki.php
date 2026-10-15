<?php
/**
 * zayvki.php — просмотр заявок пользователя
 */
session_start();

// Если не авторизован — редирект на вход
if (!isset($_SESSION['user_id'])) {
    header('Location: auth.php');
    exit;
}

require_once __DIR__ . '/../helpers/db.php';
$pdo = db_connect();

// ---------- Обработка отзыва (ТОЛЬКО при POST) ----------
$reviewSuccess = false;
$reviewError   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $reviewId    = (int)($_POST['review_id']    ?? 0);
    $comment     = trim($_POST['comment']       ?? '');
    $stars       = (int)($_POST['stars']        ?? 0);
    $requestId   = (int)($_POST['request_id']   ?? 0);

    // Проверяем, что звёзды в диапазоне 1-5
    if ($stars >= 1 && $stars <= 5 && $requestId > 0) {
        try {
            // Создаём или обновляем отзыв
            if ($reviewId > 0) {
                $stmt = $pdo->prepare("UPDATE otziv SET coment = :coment, zvezda = :zvezda WHERE id_otziv = :id");
                $stmt->execute([':coment' => $comment, ':zvezda' => $stars, ':id' => $reviewId]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO otziv (coment, zvezda) VALUES (:coment, :zvezda)");
                $stmt->execute([':coment' => $comment, ':zvezda' => $stars]);
                $reviewId = (int)$pdo->lastInsertId();
            }

            // Привязываем отзыв к заявке
            $stmt = $pdo->prepare("UPDATE zayvka SET review_id = :review_id WHERE id_request = :id AND user_id = :user_id");
            $stmt->execute([
                ':review_id' => $reviewId,
                ':id'        => $requestId,
                ':user_id'   => $_SESSION['user_id']
            ]);

            $reviewSuccess = true;
        } catch (PDOException $e) {
            $reviewError = 'Ошибка при сохранении отзыва: ' . $e->getMessage();
        }
    } else {
        $reviewError = 'Заполните отзыв корректно (1-5 звёзд)';
    }
}

// ---------- Получение заявок ----------
$sql = "SELECT 
            z.id_request,
            k.kurs AS course_name,
            s.status AS status_name,
            s.id_statusi AS status_id,
            o.oplata AS payment_name,
            z.data_nach,
            z.review_id,
            ot.coment AS review_text,
            ot.zvezda AS review_stars
        FROM zayvka z
        LEFT JOIN kursi k ON z.course_id = k.id_kursi
        LEFT JOIN statusi s ON z.status_id = s.id_statusi
        LEFT JOIN oplati o ON z.payment_id = o.id_oplati
        LEFT JOIN otziv ot ON z.review_id = ot.id_otziv
        WHERE z.user_id = :user_id
        ORDER BY z.id_request DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([':user_id' => $_SESSION['user_id']]);
$requests = $stmt->fetchAll();

// ---------- Данные для layout.php ----------
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
        <div class="success-message">✅ Отзыв успешно сохранён!</div>
    <?php endif; ?>
    <?php if ($reviewError !== ''): ?>
        <div class="error-message">❌ <?= htmlspecialchars($reviewError) ?></div>
    <?php endif; ?>

    <?php if (empty($requests)): ?>
        <div class="empty-state">
            <p>Нет вашей заявки?</p>
            <a href="new_zayvka.php" class="link-primary">Создать</a>
        </div>
    <?php else: ?>
        <div class="requests-grid">
            <?php foreach ($requests as $req): ?>
                <div class="request-card">
                    <div class="request-card-header">
                        <h3>Заявка №<?= (int)$req['id_request'] ?></h3>
                    </div>
                    <div class="request-card-body">
                        <div class="request-row">
                            <span class="label">Наименование курса:</span>
                            <span class="value"><?= htmlspecialchars($req['course_name'] ?? 'Не указан') ?></span>
                        </div>
                        <div class="request-row">
                            <span class="label">Статус:</span>
                            <span class="value status-badge"><?= htmlspecialchars($req['status_name'] ?? 'Неизвестно') ?></span>
                        </div>
                        <div class="request-row">
                            <span class="label">Способ оплаты:</span>
                            <span class="value"><?= htmlspecialchars($req['payment_name'] ?? 'Не указан') ?></span>
                        </div>
                        <div class="request-row">
                            <span class="label">Желаемая дата начала:</span>
                            <span class="value"><?= htmlspecialchars($req['data_nach'] ?? 'Не указана') ?></span>
                        </div>

                        <?php if ((int)$req['status_id'] === 3): ?>
                            <div class="request-row">
                                <span class="label">Отзыв:</span>
                                <?php if ($req['review_id']): ?>
                                    <span class="value">
                                        <?= htmlspecialchars($req['review_text'] ?? '') ?>
                                        (<?= (int)$req['review_stars'] ?> ⭐)
                                    </span>
                                <?php else: ?>
                                    <span class="value text-muted">Отзыв не оставлен</span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="request-card-footer">
                        <?php if ((int)$req['status_id'] === 3): ?>
                            <button type="button" class="btn-review"
                                    onclick="openReviewModal(
                                        <?= (int)$req['id_request'] ?>,
                                        <?= (int)($req['review_id'] ?? 0) ?>,
                                        '<?= addslashes($req['review_text'] ?? '') ?>',
                                        <?= (int)($req['review_stars'] ?? 5) ?>
                                    )">
                                <?= $req['review_id'] ? 'Изменить отзыв' : 'Оставить отзыв и звёзды' ?>
                            </button>
                        <?php else: ?>
                            <span class="text-muted">Отзыв можно оставить после завершения обучения</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
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
                       min="1" max="5" step="1"
                       value="5" required
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

<script>
function openReviewModal(requestId, reviewId, comment, stars) {
    document.getElementById('reviewRequestId').value = requestId;
    document.getElementById('reviewId').value = reviewId;
    document.getElementById('reviewComment').value = comment;
    document.getElementById('reviewStars').value = stars || 5;
    document.getElementById('reviewModal').style.display = 'flex';
}
function closeReviewModal() {
    document.getElementById('reviewModal').style.display = 'none';
}
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';