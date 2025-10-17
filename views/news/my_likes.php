<?php
  use yii\helpers\Html;
  $this->title = 'My Likes';
?>

<h3><?= Html::encode($this->title) ?></h3>

<?php if (empty($likes)): ?>
    <div class="text-muted">Belum ada thumbs up.</div>
<?php else: ?>
    <div class="list-group">
        <?php foreach ($likes as $r): 
            $url = $r->article_url;
            $title = $r->article_url; // jika ingin title, ambil dari cache artikel kalau disimpan; sederhana pakai URL
        ?>
        <a href="<?= Html::encode($url) ?>" target="_blank" class="list-group-item list-group-item-action">
            <div class="d-flex w-100 justify-content-between">
                <h5 class="mb-1"><?= Html::encode($title) ?></h5>
                <small class="text-muted"><?= Html::encode($r->created_at) ?></small>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
