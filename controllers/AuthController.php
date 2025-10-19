<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use app\models\User;
use app\models\LoginAttempt;

class AuthController extends Controller
{
    private int $maxAttempts = 5;
    private int $windowSeconds = 300;  // 5 menit
    private int $blockSeconds = 1800;  // 30 menit

    public function init()
    {
        parent::init();
        // Set timezone ke WIB (Asia/Jakarta)
        date_default_timezone_set('Asia/Jakarta');
    }

    public function actionRegister()
    {
        $model = new \yii\base\DynamicModel([
            'full_name', 'email', 'birth_year', 'password', 'password_repeat'
        ]);

        $model->addRule(['full_name', 'email', 'birth_year', 'password', 'password_repeat'], 'required');
        $model->addRule('email', 'email');

        $model->addRule('email', function ($attribute) use ($model) {
            if (User::findByEmail($model->$attribute)) {
                $model->addError($attribute, 'Email sudah digunakan.');
            }
        });

        $model->addRule('password', function ($attribute) use ($model) {
            $v = $model->$attribute;
            if (strlen($v) < 12)
                $model->addError($attribute, 'Password minimal 12 karakter.');
            if (!preg_match('/[A-Z]/', $v))
                $model->addError($attribute, 'Harus mengandung huruf besar.');
            if (!preg_match('/[0-9]/', $v))
                $model->addError($attribute, 'Harus mengandung angka.');
            if (!preg_match('/[\W_]/', $v))
                $model->addError($attribute, 'Harus mengandung simbol.');
        });

        $model->addRule('password_repeat', 'compare', [
            'compareAttribute' => 'password',
            'message' => 'Password tidak sama.'
        ]);

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $user = new User();
            $user->full_name = $model->full_name;
            $user->email = $model->email;
            $user->birth_year = (int)$model->birth_year;
            $user->setPassword($model->password);

            // 🟢 Set sebelum save
            $user->verification_token = Yii::$app->security->generateRandomString(64);
            $user->is_verified = 0;

            if ($user->save(false)) {
                try {
                    $verifyUrl = Yii::$app->urlManager->createAbsoluteUrl(['auth/verify', 'token' => $user->verification_token]);

                    $sent = Yii::$app->mailer->compose()
                        ->setTo($user->email)
                        ->setFrom(['no-reply@portalberita.com' => 'Portal Berita'])
                        ->setSubject('Verifikasi Akun Anda - Portal Berita')
                        ->setHtmlBody("
                            <p>Hai {$user->full_name},</p>
                            <p>Terima kasih sudah mendaftar di <b>Portal Berita</b>.</p>
                            <p>Klik link di bawah ini untuk verifikasi email Anda:</p>
                            <p><a href='{$verifyUrl}'>Verifikasi Email</a></p>
                            <p>Jika Anda tidak merasa mendaftar, abaikan email ini.</p>
                        ")
                        ->send();

                    if ($sent) {
                        Yii::$app->session->setFlash('success', 'Pendaftaran berhasil! Silakan cek email Anda untuk verifikasi akun.');
                    } else {
                        Yii::$app->session->setFlash('warning', 'Akun berhasil dibuat, tapi email verifikasi gagal dikirim. Silakan hubungi admin.');
                    }
                } catch (\Exception $e) {
                    Yii::$app->session->setFlash('error', 'Terjadi kesalahan saat mengirim email: ' . $e->getMessage());
                }

                return $this->render('register_success', [
                    'user' => $user,
                    'emailSent' => true
                ]);
            } else {
                Yii::$app->session->setFlash('error', 'Gagal menyimpan data pengguna.');
            }
        }

        return $this->render('register', ['model' => $model]);
    }

    public function actionCheckEmail()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $email = Yii::$app->request->post('email');
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['valid' => false, 'message' => 'Format email tidak valid.'];
        }

        if (\app\models\User::findByEmail($email)) {
            return ['valid' => false, 'message' => 'Email sudah terdaftar.'];
        }

        return ['valid' => true];
    }

    public function actionVerify($token)
    {
        $user = \app\models\User::findOne(['verification_token' => $token]);
        if (!$user) {
            Yii::$app->session->setFlash('error', 'Token verifikasi tidak valid.');
            return $this->redirect(['auth/login']);
        }

        $user->is_verified = 1;
        $user->verification_token = null;
        $user->save(false);

        Yii::$app->session->setFlash('success', 'Email berhasil diverifikasi! Anda dapat login sekarang.');
        return $this->redirect(['auth/login']);
    }

    public function actionLogin()
    {
        $request = Yii::$app->request;
        $email = $request->post('email');
        $password = $request->post('password');

        if ($request->isPost) {
            $user = User::findByEmail($email);
            $ip = $request->userIP;
            $ua = $request->userAgent;

            $attempt = new LoginAttempt();
            $attempt->email_attempt = $email;
            $attempt->ip_addr = $ip;
            $attempt->user_agent = $ua;

            if (!$user) {
                $attempt->success = false;
                $attempt->save();
                Yii::$app->session->setFlash('error', 'Email tidak terdaftar.');
                return $this->redirect(['auth/login']);
            }

            if (!$user->is_verified) {
                Yii::$app->session->setFlash(
                    'error',
                    'Email Anda belum diverifikasi. Silakan cek email untuk aktivasi.'
                );
                return $this->redirect(['auth/login']);
            }

            // 🚫 Cek apakah akun masih diblok
            if ($user->blocked_until && strtotime($user->blocked_until) > time()) {
                $attempt->user_id = $user->id;
                $attempt->success = false;
                $attempt->save();

                Yii::$app->session->setFlash(
                    'error',
                    'Terlalu banyak percobaan gagal. Akun diblok hingga ' .
                    date('Y-m-d H:i:s', strtotime($user->blocked_until))
                );
                return $this->redirect(['auth/login']);
            }

            // ✅ Validasi password
            if ($user->validatePassword($password)) {
                if ($user->blocked_until && strtotime($user->blocked_until) > time()) {
                    Yii::$app->session->setFlash(
                        'error',
                        'Akun masih diblok hingga ' .
                        date('Y-m-d H:i:s', strtotime($user->blocked_until))
                    );
                    return $this->redirect(['auth/login']);
                }

                // Reset jika sukses
                $user->failed_attempts = 0;
                $user->last_failed_at = null;
                $user->blocked_until = null;
                $user->save(false);

                $attempt->user_id = $user->id;
                $attempt->success = true;
                $attempt->save();

                Yii::$app->user->login($user, 3600 * 24 * 30);
                return $this->goHome();
            }

            // ❌ Password salah
            $user->failed_attempts = ($user->failed_attempts ?? 0) + 1;
            $user->last_failed_at = new \yii\db\Expression('NOW()');
            $user->save(false);

            $attempt->user_id = $user->id;
            $attempt->success = false;
            $attempt->save();

            $since = (new \DateTime('now', new \DateTimeZone('Asia/Jakarta')))
                ->modify('-' . $this->windowSeconds . ' seconds')
                ->format('Y-m-d H:i:s');

            $count = LoginAttempt::find()
                ->where(['user_id' => $user->id, 'success' => false])
                ->andWhere(['>=', 'created_at', $since])
                ->count();

            // 🚫 Blokir akun 30 menit dari waktu lokal
            if ($count >= $this->maxAttempts) {
                $blockedUntil = (new \DateTime('now', new \DateTimeZone('Asia/Jakarta')))
                    ->modify('+' . $this->blockSeconds . ' seconds')
                    ->format('Y-m-d H:i:s');

                $user->blocked_until = $blockedUntil;
                $user->save(false);

                Yii::$app->session->setFlash(
                    'error',
                    'Terlalu banyak percobaan gagal. Akun diblok hingga ' . $blockedUntil
                );
                return $this->redirect(['auth/login']);
            }

            Yii::$app->session->setFlash('error', 'Password salah. Percobaan ke-' . $user->failed_attempts);
            return $this->redirect(['auth/login']);
        }

        return $this->render('login');
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->goHome();
    }
}
