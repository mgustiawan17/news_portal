<?php
use yii\helpers\Html;

$this->title = 'My Likes';
?>
<h3 class="mb-3"><?= Html::encode($this->title) ?></h3>

<?php if (empty($likes)): ?>
  <div class="text-muted">Belum ada thumbs up.</div>
<?php else: ?>
  <div class="row">
    <?php foreach ($likes as $r): 
        $title = $r->article_url;
        $url = $r->article_url;
        $published = $r->created_at;
    ?>
    <div class="col-md-6 mb-3">
        <div class="card h-100 shadow-sm">
        <div class="card-body">
            <h5 class="card-title">
            <a href="<?= Html::encode($url) ?>" target="_blank" class="text-decoration-none">
                <?= Html::encode($title) ?>
            </a>
            </h5>
            <p class="card-text"><small class="text-muted">Disukai pada <?= Html::encode($published) ?></small></p>
        </div>
        </div>
    </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
