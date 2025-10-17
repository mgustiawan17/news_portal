<?php
    use yii\helpers\Html;
    use yii\helpers\Url;
?>

<h3>Pendaftaran Berhasil</h3>

<p>
    Terima kasih <strong><?= Html::encode($user->full_name) ?></strong>, 
    pendaftaran Anda berhasil.<br>
    Silakan <a href="<?= Url::to(['auth/login']) ?>">login di sini</a>.
</p>
