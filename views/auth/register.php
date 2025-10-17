<?php
  use yii\bootstrap5\ActiveForm;
  use yii\helpers\Html;

  $this->title = 'Daftar Pengguna';
?>

<div class="d-flex justify-content-center align-items-center vh-100">
  <div class="card shadow-sm p-4" style="width: 420px; border-radius: 15px;">
    <div class="text-center mb-4">
      <h4 class="fw-bold text-primary mb-1">Portal Berita</h4>
      <p class="text-muted mb-0">Buat akun baru Anda</p>
    </div>

    <?php $form = ActiveForm::begin(['options' => ['class' => 'mt-3']]); ?>

      <?= $form->field($model, 'full_name')->textInput([
          'placeholder' => 'Nama Lengkap',
          'class' => 'form-control form-control-lg rounded-pill'
      ])->label(false) ?>

      <?= $form->field($model, 'email')->input('email', [
          'placeholder' => 'Email',
          'class' => 'form-control form-control-lg rounded-pill'
      ])->label(false) ?>

      <?= $form->field($model, 'birth_year')->input('number', [
          'placeholder' => 'Tahun Lahir',
          'class' => 'form-control form-control-lg rounded-pill'
      ])->label(false) ?>

      <?= $form->field($model, 'password')->passwordInput([
          'placeholder' => 'Password',
          'class' => 'form-control form-control-lg rounded-pill'
      ])->label(false) ?>

      <?= $form->field($model, 'password_repeat')->passwordInput([
          'placeholder' => 'Ulangi Password',
          'class' => 'form-control form-control-lg rounded-pill'
      ])->label(false) ?>

      <div class="d-grid mt-4">
        <?= Html::submitButton('Daftar', [
            'class' => 'btn btn-primary btn-lg rounded-pill fw-semibold shadow-sm'
        ]) ?>
      </div>

    <?php ActiveForm::end(); ?>

    <div class="text-center mt-4">
      <small class="text-muted">Sudah punya akun?
        <?= Html::a('Login di sini', ['auth/login'], [
            'class' => 'text-primary text-decoration-none fw-semibold'
        ]) ?>
      </small>
    </div>
  </div>
</div>

<?php
  $checkEmailUrl = \yii\helpers\Url::to(['auth/check-email']);
    $js = <<<JS
      $('#dynamicmodel-email').on('blur', function() {
          const email = $(this).val();
          if (!email) return;

          $.post('$checkEmailUrl', { email: email }, function(res) {
              const input = $('#dynamicmodel-email');
              input.removeClass('is-invalid is-valid');
              input.next('.invalid-feedback, .valid-feedback').remove();

              if (res.valid) {
                  input.addClass('is-valid');
                  input.after('<div class="valid-feedback">Email tersedia ✅</div>');
              } else {
                  input.addClass('is-invalid');
                  input.after('<div class="invalid-feedback">' + res.message + '</div>');
              }
          });
      });
    JS;
  $this->registerJs($js);
?>
