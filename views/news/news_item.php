<?php
    use yii\helpers\Html;

    $img = $article['urlToImage'] ?? null;
    $url = $article['url'] ?? '#';
    $title = $article['title'] ?? 'Tanpa Judul';
    $source = $article['source']['name'] ?? '';
    $author = $article['author'] ?? '';
    $published = isset($article['publishedAt']) ? (new DateTime($article['publishedAt']))->format('Y-m-d H:i') : '';
    $desc = $article['description'] ?? '';
    $content = $article['content'] ?? '';
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
    </div>
  </div>
</div>
