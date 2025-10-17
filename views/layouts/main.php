<?php
    use yii\helpers\Html;
    use yii\helpers\Url;

    $categories = ['business','entertainment','sports','general','health','science','technology'];
?>
<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title><?= Html::encode($this->title ?: 'Portal Berita') ?></title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    </head>
    <body>
        <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom shadow-sm">
            <div class="container">
                <a class="navbar-brand fw-bold" href="<?= Url::to(['/news/index']) ?>">Portal Berita</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNews">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNews">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <?php foreach($categories as $cat): ?>
                            <li class="nav-item">
                                <a class="nav-link text-capitalize" href="<?= Url::to(['news/category','category'=>$cat]) ?>">
                                    <?= $cat ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <form class="d-flex me-3" method="get" action="<?= Url::to(['news/search']) ?>">
                        <input class="form-control me-2" type="search" name="q" placeholder="Cari berita..." required>
                        <button class="btn btn-outline-primary" type="submit">Cari</button>
                    </form>

                    <ul class="navbar-nav">
                        <?php if (Yii::$app->user->isGuest): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= Url::to(['auth/login']) ?>">Login</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= Url::to(['auth/register']) ?>">Daftar</a>
                            </li>
                        <?php else: ?>
                            <!-- Dropdown user -->
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle fw-semibold" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                    <?= Html::encode(Yii::$app->user->identity->full_name ?? 'Pengguna') ?>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="<?= Url::to(['news/my-bookmarks']) ?>">📑 My Bookmarks</a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="<?= Url::to(['news/my-likes']) ?>">👍 My Likes</a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <?= Html::beginForm(['auth/logout'], 'post')
                                            . Html::submitButton('Logout', ['class' => 'dropdown-item text-danger'])
                                            . Html::endForm(); ?>
                                    </li>
                                </ul>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="container my-4">
            <?= $content ?>
        </div>

        <!-- Modal Error -->
        <div class="modal fade" id="errorModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Terjadi Kesalahan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p id="errorMessage" class="text-danger"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="button" class="btn btn-primary" onclick="location.reload()">Muat Ulang</button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', ()=>{
                if(window.__EXCEPTION__){
                    document.getElementById('errorMessage').innerText = window.__EXCEPTION__;
                    new bootstrap.Modal(document.getElementById('errorModal')).show();
                }
            });
        </script>
    </body>
</html>
