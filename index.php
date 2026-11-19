<?php
$pageTitle = 'FoodOrder — Алматының үздік 5 мейрамханасы';
require __DIR__ . '/includes/header.php';

$rows = db()->query(
    "SELECT r.*, ROUND(AVG(v.rating),1) AS avg_rating, COUNT(v.id) AS reviews_count
     FROM restaurants r LEFT JOIN reviews v ON v.restaurant_id = r.id
     GROUP BY r.id ORDER BY avg_rating DESC, r.id"
)->fetchAll();
?>
<section class="hero">
  <div class="hero-text">
    <p class="eyebrow">Алматы · үздік 5 мейрамхана</p>
    <h1>Қай жерге барасыз?<br><span>Біз үстелді күтіп тұрамыз.</span></h1>
    <p>Мейрамхананы таңдаңыз, мәзірді қараңыз, бос кабина, VIP зал немесе столды күні мен сағатына дейін брондаңыз — бәрі үйден шықпай.</p>
    <div class="hero-actions">
      <a class="btn" href="#restaurants">Мейрамхана таңдау</a>
      <?php if (!$user): ?><a class="btn btn-ghost" href="register.php">Тіркелу</a><?php endif; ?>
    </div>
  </div>
  <div class="hero-art" aria-hidden="true">
    <div class="plate"><span class="steam s1"></span><span class="steam s2"></span><span class="steam s3"></span>🍽️</div>
  </div>
</section>

<section id="restaurants" class="section">
  <h2 class="title">Алматының топ-5 мейрамханасы</h2>
  <div class="grid">
    <?php foreach ($rows as $i => $r): ?>
      <a class="card reveal" style="--d:<?= $i * 0.1 ?>s" href="restaurant.php?slug=<?= e($r['slug']) ?>">
        <div class="card-img">
          <img src="images/<?= e($r['slug']) ?>-1.svg" alt="<?= e($r['name']) ?>" loading="lazy">
          <span class="badge">#<?= $i + 1 ?></span>
        </div>
        <div class="card-body">
          <h3><?= e($r['name']) ?></h3>
          <p class="muted"><?= e($r['cuisine']) ?></p>
          <div class="stars-line">
            <span class="stars" style="--r:<?= (float)$r['avg_rating'] ?>"></span>
            <b><?= $r['avg_rating'] !== null ? e($r['avg_rating']) : '—' ?></b>
            <small>(<?= (int)$r['reviews_count'] ?> пікір)</small>
          </div>
          <p class="muted small">🕒 <?= hhmm($r['open_time']) ?> – <?= hhmm($r['close_time']) ?></p>
          <span class="link">Брондау →</span>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<section id="how" class="section how">
  <h2 class="title">Қалай жұмыс істейді?</h2>
  <div class="steps">
    <div class="step reveal"><i>1</i><h4>Тіркеліңіз</h4><p>Бір минутта аккаунт ашыңыз.</p></div>
    <div class="step reveal" style="--d:.1s"><i>2</i><h4>Мейрамхана таңдаңыз</h4><p>Мәзір, фото және пікірлерді қараңыз.</p></div>
    <div class="step reveal" style="--d:.2s"><i>3</i><h4>Күн мен уақытты таңдаңыз</h4><p>Календарьда бос кабина, VIP зал, стол көрінеді.</p></div>
    <div class="step reveal" style="--d:.3s"><i>4</i><h4>Брондаңыз</h4><p>Растау бірден «Менің брондарым» бетінде.</p></div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
