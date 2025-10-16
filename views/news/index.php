<?php
    $this->title = 'Top Headlines (US)';
    $js = $exceptionError ? "window.__EXCEPTION__ = '".addslashes($exceptionError)."';" : "window.__EXCEPTION__ = null;";
    $this->registerJs($js, \yii\web\View::POS_HEAD);
?>

<h3 class="mb-4 fw-bold">Top Headlines — United States</h3>

<?php if ($apiError): ?>
  <div class="alert alert-warning"><pre><?= json_encode($apiError, JSON_PRETTY_PRINT) ?></pre></div>
<?php endif; ?>

<?php if (empty($articles)): ?>
  <div class="text-muted">Tidak ada berita untuk ditampilkan.</div>
<?php else: ?>
  <?php foreach($articles as $a): ?>
    <?= $this->render('news_item', ['article' => $a]) ?>
  <?php endforeach; ?>
<?php endif; ?>
