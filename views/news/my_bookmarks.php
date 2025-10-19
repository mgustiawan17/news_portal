<?php
use yii\helpers\Html;

$this->title = 'My Bookmarks';
?>
<h3 class="mb-3"><?= Html::encode($this->title) ?></h3>

<?php if (empty($bookmarks)): ?>
  <div class="text-muted">Belum ada bookmark.</div>
<?php else: ?>
  <div class="row">
    <?php foreach ($bookmarks as $bm): 
      $data = $bm->article_data ? json_decode($bm->article_data, true) : null;
      $title = $bm->article_title ?: ($data['title'] ?? $bm->article_url);
      $url = $bm->article_url;
      $img = $data['urlToImage'] ?? null;
      $source = $bm->article_source ?: ($data['source']['name'] ?? '');
      $author = $data['author'] ?? '';
      $published = isset($data['publishedAt']) ? (new DateTime($data['publishedAt']))->format('Y-m-d H:i') : $bm->created_at;
      $desc = $data['description'] ?? '';
    ?>
      <div class="col-md-6 mb-3">
        <div class="card h-100 shadow-sm">
          <div class="row g-0">
            <div class="col-md-4">
              <?php if ($img): ?>
                <img src="<?= Html::encode($img) ?>" class="img-fluid rounded-start" alt="...">
              <?php else: ?>
                <div class="bg-light d-flex align-items-center justify-content-center" style="height:100%;min-height:180px;">No Image</div>
              <?php endif; ?>
            </div>
            <div class="col-md-8">
              <div class="card-body">
                <h5 class="card-title">
                  <a href="<?= Html::encode($url) ?>" target="_blank" class="text-decoration-none"><?= Html::encode($title) ?></a>
                </h5>
                <p class="card-text"><small class="text-muted"><?= Html::encode("$source · $author · $published") ?></small></p>
                <?php if ($desc): ?><p class="card-text"><?= Html::encode($desc) ?></p><?php endif; ?>
              </div>
              <!-- Tombol disembunyikan -->
              <div class="card-footer bg-white small text-muted text-end">
                Disimpan pada <?= Html::encode($bm->created_at) ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
