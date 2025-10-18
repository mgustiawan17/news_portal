<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\HttpException;
use GuzzleHttp\Client;
use app\models\NewsCache;
use yii\web\BadRequestHttpException;
use app\models\Bookmark;
use app\models\Rating;
use yii\filters\AccessControl;
use yii\web\Response;
use app\models\ApiLog;
use yii\db\Query;

class NewsController extends Controller
{
    private Client $client;
    private string $apiKey;
    private int $cacheTtl;

    public function init()
    {
        parent::init();
        $this->client = new Client(['timeout' => 10, 'http_errors' => false]);
        $this->apiKey = $_ENV['NEWSAPI_KEY'] ?? '';
        $this->cacheTtl = $_ENV['CACHE_TTL'] ?: 600;

        Yii::info('Loaded API Key: ' . ($this->apiKey ?: 'EMPTY'), __METHOD__);
    }

    private function fetchApi($endpoint, $params = [])
    {
        Yii::info('FETCH API DIPANGGIL dengan endpoint: ' . $endpoint, __METHOD__);

        $url = "https://newsapi.org/v2/{$endpoint}";
        Yii::info('URL target: ' . $url, __METHOD__);

        try {

            $response = $this->client->get($url, [
                'headers' => [
                    'X-Api-Key' => $this->apiKey
                ],
                'query' => $params,
                'verify' => false
            ]);

            Yii::info('API response diterima.', __METHOD__);
        } catch (\Throwable $e) {
            Yii::error('Gagal panggil API: ' . $e->getMessage(), __METHOD__);
            throw $e;
        }

        $status = $response->getStatusCode();
        $body = (string)$response->getBody();
        $data = json_decode($body, true);

        Yii::info("STATUS: {$status}, BODY LENGTH: " . strlen($body), __METHOD__);

        return ['status' => $status, 'data' => $data, 'raw' => $body];
    }

    private function getRatingCounts(array $urls): array
    {
        $counts = [];
        if (empty($urls)) return $counts;

        $rows = (new Query())
            ->select([
                'article_url',
                'SUM((vote=1)::int) AS upcount',
                'SUM((vote=-1)::int) AS downcount'
            ])
            ->from('rating')
            ->where(['article_url' => $urls])
            ->groupBy('article_url')
            ->all();

        foreach ($rows as $r) {
            $counts[$r['article_url']] = [
                'up' => (int)$r['upcount'],
                'down' => (int)$r['downcount'],
            ];
        }

        return $counts;
    }

    public function actionIndex()
    {
        $api = null; 
        if (!$api) {
            $api = $this->fetchApi('top-headlines', ['country' => 'us', 'pageSize' => 10]);
        }

        $articles = $api['data']['articles'] ?? [];
        $urls = array_values(array_unique(array_filter(array_map(fn($a) => $a['url'] ?? null, $articles))));
        $counts = $this->getRatingCounts($urls);

        $userBookmarks = [];
        if (!Yii::$app->user->isGuest) {
            $userBookmarks = Bookmark::find()
                ->select('article_url')
                ->where(['user_id' => Yii::$app->user->id])
                ->column(); // hasilnya: array of URL artikel
        }

        return $this->render('index', [
            'articles' => $articles,
            'counts' => $counts,
            'userBookmarks' => $userBookmarks,
            'apiError' => $api['status'] !== 200 ? $api : null,
            'exceptionError' => null
        ]);
    }

    public function actionCategory(string $category)
    {
        $allowed = ['business','entertainment','sports','general','health','science','technology'];
        if (!in_array($category, $allowed)) throw new HttpException(404, 'Kategori tidak ditemukan');

        $api = null;
        if (!$api) {
            $api = $this->fetchApi('top-headlines', ['country' => 'us', 'category' => $category, 'pageSize' => 50]);
        }

        $articles = $api['data']['articles'] ?? [];
        $urls = array_values(array_unique(array_filter(array_map(fn($a) => $a['url'] ?? null, $articles))));
        $counts = $this->getRatingCounts($urls);

        $userBookmarks = [];
        if (!Yii::$app->user->isGuest) {
            $userBookmarks = Bookmark::find()
                ->select('article_url')
                ->where(['user_id' => Yii::$app->user->id])
                ->column(); // hasilnya: array of URL artikel
        }

        return $this->render('category', [
            'category' => $category,
            'articles' => $articles,
            'counts' => $counts,
            'userBookmarks' => $userBookmarks,
            'apiError' => $api['status'] !== 200 ? $api : null,
            'exceptionError' => null
        ]);
    }

    public function actionSearch($q)
    {
        try {
            Yii::info("Mulai pencarian keyword: {$q}", __METHOD__);

            // Panggil API NewsAPI langsung tanpa cache
            $api = $this->fetchApi('everything', [
                'q' => $q,
                'language' => 'en', // bisa ubah ke 'id' kalau ingin berita bahasa Indonesia
                'sortBy' => 'publishedAt',
                'pageSize' => 20,
            ]);

            // Cek apakah API sukses
            if ($api['status'] !== 200) {
                Yii::error("Gagal ambil hasil pencarian: " . json_encode($api), __METHOD__);
                return $this->render('search', [
                    'query' => $q,
                    'articles' => [],
                    'apiError' => $api,
                    'exceptionError' => null
                ]);
            }

            // Ambil daftar artikel
            $articles = $api['data']['articles'] ?? [];

            // Jika kosong, beri pesan
            if (empty($articles)) {
                Yii::warning("Tidak ada hasil ditemukan untuk: {$q}", __METHOD__);
                return $this->render('search', [
                    'query' => $q,
                    'articles' => [],
                    'apiError' => null,
                    'exceptionError' => null
                ]);
            }

            // Hitung like/dislike (optional)
            $urls = array_values(array_unique(array_filter(array_map(fn($a) => $a['url'] ?? null, $articles))));
            $counts = $this->getRatingCounts($urls);
            $userBookmarks = [];
            if (!Yii::$app->user->isGuest) {
                $userBookmarks = Bookmark::find()
                    ->select('article_url')
                    ->where(['user_id' => Yii::$app->user->id])
                    ->column(); // hasilnya: array of URL artikel
            }

            return $this->render('search', [
                'query' => $q,
                'articles' => $articles,
                'counts' => $counts,
                'userBookmarks' => $userBookmarks,
                'apiError' => null,
                'exceptionError' => null
            ]);
        } catch (\Throwable $e) {
            Yii::error("Error pencarian: " . $e->getMessage(), __METHOD__);
            return $this->render('search', [
                'query' => $q,
                'articles' => [],
                'apiError' => null,
                'exceptionError' => $e->getMessage()
            ]);
        }
    }

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => \yii\filters\AccessControl::class,
                'only' => ['bookmark', 'rate', 'my-bookmarks', 'my-likes'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['bookmark', 'rate', 'my-bookmarks', 'my-likes'],
                        'roles' => ['@'], // @ = harus login
                    ],
                ],
            ],
        ]);
    }

    // Bookmark
    public function actionBookmark()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $data = json_decode(Yii::$app->request->getRawBody(), true);
        $userId = Yii::$app->user->id;

        if (empty($data['article_url'])) throw new BadRequestHttpException('article_url required');

        $url = $data['article_url'];
        $existing = Bookmark::findOne(['user_id'=>$userId,'article_url'=>$url]);

        if ($existing) {
            return ['success'=>true,'message'=>'Sudah terbookmark'];
        }

        $bm = new Bookmark();
        $bm->user_id = $userId;
        $bm->article_url = $url;
        $bm->article_title = $data['article']['title'] ?? null;
        $bm->article_source = $data['article']['source']['name'] ?? null;
        $bm->article_data = isset($data['article']) ? json_encode($data['article']) : null;

        if ($bm->save()) {
            return ['success'=>true];
        }
        
        return ['success'=>false,'message'=>'Gagal menyimpan bookmark'];
    }

    // Rate
    public function actionRate()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $data = json_decode(Yii::$app->request->getRawBody(), true);
        $userId = Yii::$app->user->id;

        if (!isset($data['article_url']) || !isset($data['vote'])) throw new BadRequestHttpException('invalid payload');
        
        $url = $data['article_url'];
        $vote = (int)$data['vote'];
        
        if (!in_array($vote, [1,-1])) throw new BadRequestHttpException('vote invalid');

        $existing = Rating::findOne(['user_id'=>$userId,'article_url'=>$url]);
        if ($existing) {
            $existing->vote = $vote;
            $existing->save(false);
        } else {
            $r = new Rating();
            $r->user_id = $userId;
            $r->article_url = $url;
            $r->vote = $vote;
            $r->save(false);
        }

        // recompute aggregates
        $upCount = (int) Rating::find()->where(['article_url' => $url, 'vote' => 1])->count();
        $downCount = (int) Rating::find()->where(['article_url' => $url, 'vote' => -1])->count();

        return ['success'=>true,'upCount'=>$upCount,'downCount'=>$downCount];
    }

    // My bookmarks page
    public function actionMyBookmarks()
    {
        $userId = Yii::$app->user->id;
        $bookmarks = Bookmark::find()->where(['user_id'=>$userId])->orderBy(['created_at'=>SORT_DESC])->all();
        return $this->render('my_bookmarks',['bookmarks'=>$bookmarks]);
    }

    // My likes (thumbs up)
    public function actionMyLikes()
    {
        $userId = Yii::$app->user->id;
        $likes = Rating::find()->where(['user_id'=>$userId,'vote'=>1])->orderBy(['created_at'=>SORT_DESC])->all();
        return $this->render('my_likes',['likes'=>$likes]);
    }
}
