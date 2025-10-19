<?php
use yii\helpers\Html;

/** @var app\models\User $user */
/** @var bool $emailSent */

$this->title = 'Pendaftaran Berhasil';
?>
<div class="site-register-success text-center">
    <h1><?= Html::encode($this->title) ?></h1>
    <p>Hai <b><?= Html::encode($user->full_name) ?></b>,</p>

    <?php if ($emailSent): ?>
        <p>Pendaftaran Anda berhasil! Silakan cek email Anda (<?= Html::encode($user->email) ?>) untuk melakukan verifikasi akun.</p>
    <?php else: ?>
        <p>Akun berhasil dibuat, namun email verifikasi gagal dikirim. Silakan hubungi admin untuk aktivasi manual.</p>
    <?php endif; ?>

    <p><?= Html::a('Kembali ke Login', ['auth/login'], ['class' => 'btn btn-primary mt-3']) ?></p>
</div>
