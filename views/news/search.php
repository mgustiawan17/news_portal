<?php
    use yii\helpers\Html;

    $this->title = 'Cari: ' . Html::encode($query);
    $js = $exceptionError ? "window.__EXCEPTION__ = '".addslashes($exceptionError)."';" : "window.__EXCEPTION__ = null;";
    $this->registerJs($js, \yii\web\View::POS_HEAD);
?>

<h3 class="mb-4 fw-bold">Hasil Pencarian: <?= Html::encode($query) ?></h3>

<?php if ($apiError): ?>
  <div class="alert alert-warning"><pre><?= json_encode($apiError, JSON_PRETTY_PRINT) ?></pre></div>
<?php endif; ?>

<?php if (empty($articles)): ?>
  <div class="text-muted">Tidak ada hasil ditemukan.</div>
<?php else: ?>
  <?php foreach($articles as $a): ?>
    <?= $this->render('news_item', [
          'article' => $a,
          'userBookmarks' => $userBookmarks ?? [],
          'counts' => $counts ?? [],
    ]) ?>
  <?php endforeach; ?>
<?php endif; ?>
