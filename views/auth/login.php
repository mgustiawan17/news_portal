<?php
  use yii\bootstrap5\ActiveForm;
  use yii\helpers\Html;

  $this->title = 'Login';
?>

<div class="d-flex justify-content-center align-items-center vh-100">
  <div class="card shadow-sm p-4" style="width: 380px; border-radius: 15px;">
    <div class="text-center mb-4">
      <h4 class="fw-bold text-primary mb-1">Portal Berita</h4>
      <p class="text-muted mb-0">Masuk ke akun Anda</p>
    </div>

    <?php if (Yii::$app->session->hasFlash('error')): ?>
      <div class="alert alert-danger text-center">
        <?= Yii::$app->session->getFlash('error') ?>
      </div>
    <?php endif; ?>

    <?php $form = ActiveForm::begin(['options' => ['class' => 'mt-3']]); ?>

      <?= $form->field(new \yii\base\DynamicModel(['email' => '', 'password' => '']), 'email')
        ->textInput([
          'name' => 'email',
          'placeholder' => 'Email',
          'class' => 'form-control form-control-lg rounded-pill'
        ])->label(false) ?>

      <?= $form->field(new \yii\base\DynamicModel(['email' => '', 'password' => '']), 'password')
        ->passwordInput([
          'name' => 'password',
          'placeholder' => 'Password',
          'class' => 'form-control form-control-lg rounded-pill'
        ])->label(false) ?>

      <div class="d-grid mt-4">
        <?= Html::submitButton('Login', [
          'class' => 'btn btn-primary btn-lg rounded-pill fw-semibold shadow-sm'
        ]) ?>
      </div>

    <?php ActiveForm::end(); ?>

    <div class="text-center mt-4">
      <small class="text-muted">Belum punya akun?
        <?= Html::a('Daftar di sini', ['register'], ['class' => 'text-primary text-decoration-none fw-semibold']) ?>
      </small>
    </div>
  </div>
</div>
