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

<?php
  $this->registerJs(<<<JS
    $('.btn-bookmark').on('click', function() {
      const url = $(this).data('url');
      $.ajax({
        url: '/news/bookmark',
        method: 'POST',
        data: JSON.stringify({ article_url: url }),
        contentType: 'application/json',
        success: res => {
          alert(res.message || 'Bookmark berhasil!');
        },
        error: err => alert('Silahkan login terlebih dahulu untuk bookmark.');
      });
    });

    $('.btn-thumb-up, .btn-thumb-down').on('click', function() {
      const url = $(this).data('url');
      const vote = $(this).hasClass('btn-thumb-up') ? 1 : -1;
      $.ajax({
        url: '/news/rate',
        method: 'POST',
        data: JSON.stringify({ article_url: url, vote }),
        contentType: 'application/json',
        success: res => {
          if (res.success) {
            const card = $(this).closest('.card');
            card.find('.thumb-up-count').text(res.upCount);
            card.find('.thumb-down-count').text(res.downCount);
          }
        },
        error: () => alert('Silahkan login terlebih dahulu untuk memberikan rating.')
      });
    });
  JS);
?>

