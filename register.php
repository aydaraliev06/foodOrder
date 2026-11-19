<?php
require_once __DIR__ . '/config.php';
$errors = [];
$name = $email = $phone = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok($_POST['csrf'] ?? null)) { $errors[] = 'Сессия мерзімі өтті, қайта көріңіз.'; }
    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $pass2 = $_POST['password2'] ?? '';

    if (mb_strlen($name) < 2) $errors[] = 'Атыңызды жазыңыз.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email дұрыс емес.';
    if (strlen($pass) < 6) $errors[] = 'Құпиясөз кемінде 6 таңба болсын.';
    if ($pass !== $pass2) $errors[] = 'Құпиясөздер сәйкес емес.';

    if (!$errors) {
        $st = db()->prepare('SELECT id FROM users WHERE email=?');
        $st->execute([$email]);
        if ($st->fetch()) {
            $errors[] = 'Бұл email бұрын тіркелген.';
        } else {
            db()->prepare('INSERT INTO users (name,email,phone,password_hash) VALUES (?,?,?,?)')
                ->execute([$name, $email, $phone, password_hash($pass, PASSWORD_DEFAULT)]);
            session_regenerate_id(true);
            $_SESSION['uid'] = (int)db()->lastInsertId();
            header('Location: index.php');
            exit;
        }
    }
}
$pageTitle = 'Тіркелу — FoodOrder';
require __DIR__ . '/includes/header.php';
?>
<section class="auth">
  <form method="post" class="auth-card">
    <h2>Тіркелу</h2>
    <?php foreach ($errors as $er): ?><p class="error"><?= e($er) ?></p><?php endforeach; ?>
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <label>Аты-жөні <input name="name" value="<?= e($name) ?>" required></label>
    <label>Email <input type="email" name="email" value="<?= e($email) ?>" required></label>
    <label>Телефон <input name="phone" value="<?= e($phone) ?>" placeholder="+7 7xx xxx xx xx"></label>
    <label>Құпиясөз <input type="password" name="password" required minlength="6"></label>
    <label>Құпиясөзді қайталаңыз <input type="password" name="password2" required></label>
    <button class="btn" type="submit">Тіркелу</button>
    <p class="muted">Аккаунт бар ма? <a href="login.php">Кіру</a></p>
  </form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
