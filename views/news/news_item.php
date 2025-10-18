<?php
  use yii\helpers\Html;
  use yii\helpers\Url;

  $article_url = $article['url'] ?? '';
  $img = $article['urlToImage'] ?? null;
  $url = $article['url'] ?? '#';
  $title = $article['title'] ?? 'Tanpa Judul';
  $source = $article['source']['name'] ?? '';
  $author = $article['author'] ?? '';
  $published = isset($article['publishedAt']) ? (new DateTime($article['publishedAt']))->format('Y-m-d H:i') : '';
  $desc = $article['description'] ?? '';
  $content = $article['content'] ?? '';
  $article_json = base64_encode(json_encode($article, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
  $isBookmarked = in_array($article['url'], $userBookmarks);

  // counts tersedia jika controller memberikan 'counts' array
  $up = $counts[$article_url]['up'] ?? 0;
  $down = $counts[$article_url]['down'] ?? 0;
  $this->registerJs("
    window.userBookmarks = " . json_encode($userBookmarks) . ";
  ");
?>


<div class="card mb-3 shadow-sm">
  <div class="row g-0">
    <div class="col-md-3">
      <?php if($img): ?>
        <img src="<?= Html::encode($img) ?>" class="img-fluid rounded-start" alt="...">
      <?php else: ?>
        <div class="bg-light d-flex align-items-center justify-content-center" style="height:100%;min-height:180px;">No Image</div>
      <?php endif; ?>
    </div>
    <div class="col-md-9">
      <div class="card-body">
        <h5 class="card-title"><a href="<?= Html::encode($url) ?>" target="_blank" class="text-decoration-none"><?= Html::encode($title) ?></a></h5>
        <p class="card-text"><small class="text-muted"><?= Html::encode("$source · $author · $published") ?></small></p>
        <?php if($desc): ?><p><?= Html::encode($desc) ?></p><?php endif; ?>
        <?php if($content): ?><p class="text-secondary small"><?= Html::encode($content) ?></p><?php endif; ?>
      </div>

      <div class="card-footer bg-white d-flex gap-2 align-items-center">
        <?php if (!Yii::$app->user->isGuest): ?>
            <button class="btn btn-sm <?= $isBookmarked ? 'btn-primary' : 'btn-outline-primary' ?> btn-bookmark" data-url="<?= Html::encode($article_url) ?>" data-article="<?= $article_json ?>"><?= $isBookmarked ? 'Bookmarked' : 'Bookmark' ?></button>
            <button class="btn btn-outline-success btn-sm btn-thumb-up" data-url="<?= Html::encode($article_url) ?>">👍 <span class="thumb-up-count"><?= $up ?></span></button>
            <button class="btn btn-outline-danger btn-sm btn-thumb-down" data-url="<?= Html::encode($article_url) ?>">👎 <span class="thumb-down-count"><?= $down ?></span></button>
        <?php else: ?>
            <a href="<?= Url::to(['auth/login']) ?>" class="btn btn-outline-secondary btn-sm">Login untuk bookmark & rating</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('click', function(e){
    // Bookmark
    const b = e.target.closest('.btn-bookmark');
    if (b) {
      let article = {};
      try {
        // decode base64 -> parse JSON
        const decoded = atob(b.dataset.article || '');
        article = JSON.parse(decoded);
      } catch (err) {
        console.error('Gagal parse data-article:', b.dataset.article);
        alert('Gagal membaca data artikel');
        return;
      }

      const url = b.dataset.url;
      fetch('<?= \yii\helpers\Url::to(['news/bookmark']) ?>', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-Token': '<?= Yii::$app->request->csrfToken ?>'
        },
        body: JSON.stringify({ article_url: url, article })
      })
      .then(r => r.json())
      .then(j => {
        if (j.success) {
          b.classList.remove('btn-outline-primary');
          b.classList.add('btn-primary');
          b.innerText = 'Bookmarked';
        } else {
          alert(j.message || 'Gagal');
        }
      })
      .catch(err => console.error('Fetch error:', err));
    }

    // Thumb up/down
    const up = e.target.closest('.btn-thumb-up');
    const down = e.target.closest('.btn-thumb-down');
    if (up || down) {
      const isUp = !!up;
      const btn = isUp ? up : down;
      const url = btn.dataset.url;
      fetch('<?= \yii\helpers\Url::to(['news/rate']) ?>', {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-Token':'<?= Yii::$app->request->csrfToken ?>'},
        body: JSON.stringify({article_url: url, vote: isUp ? 1 : -1})
      }).then(r=>r.json()).then(j=>{
        if (j.success) {
          if (j.upCount !== undefined) {
            btn.parentElement.querySelector('.thumb-up-count').innerText = j.upCount;
          }
          if (j.downCount !== undefined) {
            btn.parentElement.querySelector('.thumb-down-count').innerText = j.downCount;
          }
        } else {
          alert(j.message || 'Gagal memberi rating');
        }
      }).catch(err=>console.error(err));
    }
  });
</script>
