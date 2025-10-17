<?php
  use yii\helpers\Html;
  $this->title = 'My Bookmarks';
?>

<h3><?= Html::encode($this->title) ?></h3>

<?php if (empty($bookmarks)): ?>
    <div class="text-muted">Belum ada bookmark.</div>
<?php else: ?>
    <div class="list-group">
        <?php foreach ($bookmarks as $bm): 
            $data = $bm->article_data ? json_decode($bm->article_data, true) : null;
            $title = $bm->article_title ?: ($data['title'] ?? $bm->article_url);
            $url = $bm->article_url;
            $source = $bm->article_source ?: ($data['source']['name'] ?? '');
        ?>
        <a href="<?= Html::encode($url) ?>" target="_blank" class="list-group-item list-group-item-action">
            <div class="d-flex w-100 justify-content-between">
                <h5 class="mb-1"><?= Html::encode($title) ?></h5>
                <small class="text-muted"><?= Html::encode($bm->created_at) ?></small>
            </div>
            <p class="mb-1 text-muted"><?= Html::encode($source) ?></p>
        </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
