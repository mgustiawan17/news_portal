<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\HttpException;
use GuzzleHttp\Client;
use app\models\NewsCache;

class NewsController extends Controller
{
    private Client $client;
    private string $apiKey;
    private int $cacheTtl;

    public function init()
    {
        parent::init();
        $this->client = new Client(['timeout' => 10, 'http_errors' => false]);
        $this->apiKey = getenv('NEWSAPI_KEY') ?? '';
        $this->cacheTtl = getenv('CACHE_TTL') ?: 600;
    }

    private function getCache($key)
    {
        $cache = NewsCache::findByKey($key);
        if (!$cache) return null;
        if (time() - strtotime($cache->created_at) > $this->cacheTtl) return null;
        return $cache->response;
    }

    private function setCache($key, $response)
    {
        NewsCache::set($key, $response);
    }

    private function fetchApi($endpoint, $params)
    {
        $params['apiKey'] = $this->apiKey;
        $res = $this->client->get("https://newsapi.org/v2/{$endpoint}", ['query' => $params]);
        $data = json_decode($res->getBody(), true);
        return ['status' => $res->getStatusCode(), 'data' => $data];
    }

    public function actionIndex()
    {
        try {
            $cacheKey = 'top_us';
            $api = $this->getCache($cacheKey);
            if (!$api) {
                $api = $this->fetchApi('top-headlines', ['country' => 'us', 'pageSize' => 10]);
                $this->setCache($cacheKey, $api);
            }

            if ($api['status'] !== 200)
                return $this->render('index', ['articles' => [], 'apiError' => $api, 'exceptionError' => null]);

            return $this->render('index', [
                'articles' => $api['data']['articles'] ?? [],
                'apiError' => null,
                'exceptionError' => null
            ]);
        } catch (\Throwable $e) {
            return $this->render('index', ['articles' => [], 'apiError' => null, 'exceptionError' => $e->getMessage()]);
        }
    }

    public function actionCategory($category)
    {
        $allowed = ['business', 'entertainment', 'sports', 'general', 'health', 'science', 'technology'];
        if (!in_array($category, $allowed)) throw new HttpException(404, 'Kategori tidak ditemukan');

        try {
            $cacheKey = "cat_$category";
            $api = $this->getCache($cacheKey);
            if (!$api) {
                $api = $this->fetchApi('top-headlines', ['country' => 'us', 'category' => $category, 'pageSize' => 100]);
                $this->setCache($cacheKey, $api);
            }

            if ($api['status'] !== 200)
                return $this->render('category', ['category' => $category, 'articles' => [], 'apiError' => $api, 'exceptionError' => null]);

            $today = date('Y-m-d');
            $articles = array_filter($api['data']['articles'], function($a) use ($today) {
                return isset($a['publishedAt']) && strpos($a['publishedAt'], $today) !== false;
            });

            return $this->render('category', [
                'category' => $category,
                'articles' => $articles,
                'apiError' => null,
                'exceptionError' => null
            ]);
        } catch (\Throwable $e) {
            return $this->render('category', ['category' => $category, 'articles' => [], 'apiError' => null, 'exceptionError' => $e->getMessage()]);
        }
    }

    public function actionSearch($q)
    {
        try {
            $cacheKey = 'search_' . md5($q);
            $api = $this->getCache($cacheKey);
            if (!$api) {
                $api = $this->fetchApi('everything', ['q' => $q, 'sortBy' => 'publishedAt', 'pageSize' => 100]);
                $this->setCache($cacheKey, $api);
            }

            if ($api['status'] !== 200)
                return $this->render('search', ['query' => $q, 'articles' => [], 'apiError' => $api, 'exceptionError' => null]);

            return $this->render('search', [
                'query' => $q,
                'articles' => $api['data']['articles'] ?? [],
                'apiError' => null,
                'exceptionError' => null
            ]);
        } catch (\Throwable $e) {
            return $this->render('search', ['query' => $q, 'articles' => [], 'apiError' => null, 'exceptionError' => $e->getMessage()]);
        }
    }
}
