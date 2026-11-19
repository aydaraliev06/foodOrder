<?php
require_once __DIR__ . '/config.php';
$st = db()->prepare('SELECT * FROM restaurants WHERE slug = ?');
$st->execute([$_GET['slug'] ?? '']);
$r = $st->fetch();
if (!$r) { http_response_code(404); exit('Мейрамхана табылмады. <a href="index.php">Басты бет</a>'); }

$pageTitle = $r['name'] . ' — FoodOrder';
require __DIR__ . '/includes/header.php';
$rid = (int)$r['id'];

$photos = db()->prepare('SELECT * FROM photos WHERE restaurant_id=?'); $photos->execute([$rid]); $photos = $photos->fetchAll();
$spaces = db()->prepare("SELECT * FROM spaces WHERE restaurant_id=? ORDER BY FIELD(type,'table','cabin','vip'), id"); $spaces->execute([$rid]); $spaces = $spaces->fetchAll();
$menu = db()->prepare('SELECT * FROM menu_items WHERE restaurant_id=? ORDER BY price'); $menu->execute([$rid]);
$byCat = ['food' => [], 'drink' => [], 'dessert' => []];
foreach ($menu->fetchAll() as $m) $byCat[$m['category']][] = $m;
$revs = db()->prepare('SELECT v.*, u.name FROM reviews v JOIN users u ON u.id=v.user_id WHERE v.restaurant_id=? ORDER BY v.id DESC'); $revs->execute([$rid]); $revs = $revs->fetchAll();
$avg = $revs ? round(array_sum(array_column($revs, 'rating')) / count($revs), 1) : 0;
?>
<section class="rest-hero">
  <img src="images/<?= e($r['slug']) ?>-1.svg" alt="">
  <div class="rest-hero-text">
    <h1><?= e($r['name']) ?></h1>
    <p><?= e($r['cuisine']) ?> · <?= e($r['atmosphere']) ?></p>
    <div class="stars-line"><span class="stars" style="--r:<?= $avg ?>"></span><b><?= $avg ?: '—' ?></b><small>(<?= count($revs) ?> пікір)</small></div>
  </div>
</section>

<section class="section narrow">
  <p class="lead"><?= e($r['description']) ?></p>
  <ul class="info">
    <li>📍 <?= e($r['address']) ?></li>
    <li>📞 <?= e($r['phone']) ?></li>
    <li>🕒 <?= hhmm($r['open_time']) ?> – <?= hhmm($r['close_time']) ?></li>
  </ul>
</section>

<div class="tabs" id="tabs">
  <button class="active" data-tab="photos"> Фотолар</button>
  <button data-tab="menu"> Мәзір</button>
  <button data-tab="book"> Брондау</button>
  <button data-tab="reviews"> Пікірлер</button>
</div>

<!-- ФОТОЛАР -->
<section class="tab-panel section active" id="tab-photos">
  <div class="gallery">
    <?php foreach ($photos as $p): ?>
      <figure class="reveal"><img src="images/<?= e($p['file']) ?>" alt="<?= e($p['caption']) ?>" loading="lazy"><figcaption><?= e($p['caption']) ?></figcaption></figure>
    <?php endforeach; ?>
  </div>
</section>

<!-- МӘЗІР -->
<section class="tab-panel section" id="tab-menu">
  <?php foreach ($byCat as $cat => $items): if (!$items) continue; ?>
    <h3 class="cat-title"><?= CAT_LABELS[$cat] ?></h3>
    <div class="menu-grid">
      <?php foreach ($items as $m): ?>
        <div class="menu-item reveal">
          <div><b><?= e($m['name']) ?></b><?php if ($m['description']): ?><small><?= e($m['description']) ?></small><?php endif; ?></div>
          <span class="price"><?= number_format($m['price'], 0, '', ' ') ?> ₸</span>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endforeach; ?>
</section>

<!-- БРОНДАУ -->
<section class="tab-panel section" id="tab-book">
  <?php if (!$user): ?>
    <div class="notice">Брондау үшін алдымен <a href="login.php?next=<?= urlencode('restaurant.php?slug=' . $r['slug']) ?>">кіріңіз</a> немесе <a href="register.php">тіркеліңіз</a>.</div>
  <?php endif; ?>
  <div class="book" id="book"
       data-open="<?= hhmm($r['open_time']) ?>" data-close="<?= hhmm($r['close_time']) ?>"
       data-logged="<?= $user ? 1 : 0 ?>">
    <div class="book-left">
      <h3>1. Орынды таңдаңыз</h3>
      <div class="spaces" id="spaces">
        <?php foreach ($spaces as $i => $s): ?>
          <button type="button" class="space<?= $i === 0 ? ' active' : '' ?>" data-id="<?= (int)$s['id'] ?>" data-cap="<?= (int)$s['capacity'] ?>">
            <b><?= e($s['name']) ?></b>
            <small><?= TYPE_LABELS[$s['type']] ?> · <?= (int)$s['capacity'] ?> адамға дейін</small>
          </button>
        <?php endforeach; ?>
      </div>
      <h3>2. Күнді таңдаңыз</h3>
      <div class="cal">
        <div class="cal-head">
          <button type="button" id="cal-prev">‹</button><b id="cal-title"></b><button type="button" id="cal-next">›</button>
        </div>
        <div class="cal-week"><span>Дс</span><span>Сс</span><span>Ср</span><span>Бс</span><span>Жм</span><span>Сб</span><span>Жк</span></div>
        <div class="cal-days" id="cal-days"></div>
        <div class="legend"><i class="l-free"></i> бос <i class="l-part"></i> жартылай бос <i class="l-full"></i> толық бос емес</div>
      </div>
    </div>
    <div class="book-right">
      <h3>3. Уақытты таңдаңыз</h3>
      <p class="muted" id="day-label">Алдымен күнді таңдаңыз</p>
      <div id="busy-list" class="busy-list"></div>
      <div class="hours" id="hours"></div>
      <div class="row">
        <label>Ұзақтығы
          <select id="duration"><option value="1">1 сағат</option><option value="2" selected>2 сағат</option><option value="3">3 сағат</option><option value="4">4 сағат</option></select>
        </label>
        <label>Қонақ саны <input type="number" id="guests" min="1" value="2"></label>
      </div>
      <label>Ескертпе (міндетті емес) <input type="text" id="note" maxlength="200" placeholder="Мысалы: туған күн"></label>
      <div class="summary" id="summary"></div>
      <button class="btn" id="book-btn" type="button">Брондау</button>
      <p id="book-msg" class="msg-line"></p>
    </div>
  </div>
</section>

<!-- ПІКІРЛЕР -->
<section class="tab-panel section narrow" id="tab-reviews">
  <?php if ($user): ?>
    <form id="review-form" class="review-form" data-rid="<?= $rid ?>">
      <h3>Пікір қалдыру</h3>
      <div class="star-input" id="star-input">
        <?php for ($i = 5; $i >= 1; $i--): ?>
          <input type="radio" name="rating" id="st<?= $i ?>" value="<?= $i ?>"><label for="st<?= $i ?>" title="<?= $i ?> жұлдыз">★</label>
        <?php endfor; ?>
      </div>
      <textarea name="comment" rows="3" maxlength="1000" placeholder="Әсерлеріңізбен бөлісіңіз..." required></textarea>
      <button class="btn" type="submit">Жіберу</button>
      <p id="review-msg" class="msg-line"></p>
    </form>
  <?php else: ?>
    <div class="notice">Пікір жазу үшін <a href="login.php?next=<?= urlencode('restaurant.php?slug=' . $r['slug']) ?>">кіріңіз</a>.</div>
  <?php endif; ?>
  <div id="review-list">
    <?php foreach ($revs as $v): ?>
      <article class="review reveal">
        <div class="stars-line"><b><?= e($v['name']) ?></b><span class="stars" style="--r:<?= (int)$v['rating'] ?>"></span><small><?= e(substr($v['created_at'], 0, 10)) ?></small></div>
        <p><?= nl2br(e($v['comment'])) ?></p>
      </article>
    <?php endforeach; ?>
    <?php if (!$revs): ?><p class="muted">Әзірге пікір жоқ. Бірінші болыңыз!</p><?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
