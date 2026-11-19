<?php
require_once __DIR__ . '/config.php';
$error = '';
$next = $_GET['next'] ?? $_POST['next'] ?? 'index.php';
// ашық redirect-тен қорғану: тек жергілікті бет
if (!preg_match('/^[a-z_]+\.php(\?[\w=&%-]*)?$/i', $next)) $next = 'index.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok($_POST['csrf'] ?? null)) {
        $error = 'Сессия мерзімі өтті, қайта көріңіз.';
    } else {
        $st = db()->prepare('SELECT id,password_hash FROM users WHERE email=?');
        $st->execute([trim($_POST['email'] ?? '')]);
        $u = $st->fetch();
        if ($u && password_verify($_POST['password'] ?? '', $u['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['uid'] = (int)$u['id'];
            header('Location: ' . $next);
            exit;
        }
        $error = 'Email немесе құпиясөз қате.';
    }
}
$pageTitle = 'Кіру — FoodOrder';
require __DIR__ . '/includes/header.php';
?>
<section class="auth">
  <form method="post" class="auth-card">
    <h2>Кіру</h2>
    <?php if ($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <input type="hidden" name="next" value="<?= e($next) ?>">
    <label>Email <input type="email" name="email" required></label>
    <label>Құпиясөз <input type="password" name="password" required></label>
    <button class="btn" type="submit">Кіру</button>
    <p class="muted">Аккаунт жоқ па? <a href="register.php">Тіркелу</a></p>
    <p class="muted small">Демо: aigerim@example.kz / demo1234</p>
  </form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
