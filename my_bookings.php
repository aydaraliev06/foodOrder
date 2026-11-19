<?php
require_once __DIR__ . '/config.php';
if (!current_user()) { header('Location: login.php?next=my_bookings.php'); exit; }
$pageTitle = 'Менің брондарым — FoodOrder';
require __DIR__ . '/includes/header.php';

$st = db()->prepare(
    "SELECT b.*, r.name AS rname, r.slug, s.name AS sname, s.type
     FROM bookings b JOIN restaurants r ON r.id=b.restaurant_id JOIN spaces s ON s.id=b.space_id
     WHERE b.user_id=? ORDER BY b.booking_date DESC, b.start_time DESC"
);
$st->execute([$user['id']]);
$rows = $st->fetchAll();
?>
<section class="section narrow">
  <h2 class="title">Менің брондарым</h2>
  <?php if (!$rows): ?><p class="muted">Әзірге бронь жоқ. <a href="index.php#restaurants">Мейрамхана таңдаңыз</a>.</p><?php endif; ?>
  <?php foreach ($rows as $b): ?>
    <div class="booking-card <?= $b['status'] === 'cancelled' ? 'cancelled' : '' ?>" data-id="<?= (int)$b['id'] ?>">
      <div>
        <h3><a href="restaurant.php?slug=<?= e($b['slug']) ?>"><?= e($b['rname']) ?></a></h3>
        <p><?= e($b['sname']) ?> (<?= TYPE_LABELS[$b['type']] ?>) · <?= (int)$b['guests'] ?> адам</p>
        <p><b><?= e($b['booking_date']) ?></b>, <?= hhmm($b['start_time']) ?> – <?= hhmm($b['end_time']) ?></p>
      </div>
      <div>
        <?php if ($b['status'] === 'active' && $b['booking_date'] >= date('Y-m-d')): ?>
          <button class="btn btn-small btn-danger cancel-btn" data-id="<?= (int)$b['id'] ?>">Бас тарту</button>
        <?php else: ?>
          <span class="muted"><?= $b['status'] === 'cancelled' ? 'Бас тартылған' : 'Өтті' ?></span>
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
