<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

class RequestService
{
    /**
     * 是否驗證 SSL 有效性
     *
     * @var bool
     */
    protected $verify_ssl;

    /**
     * Single Sign On Service Uri
     *
     * @var string
     */
    protected $sso_uri;

    /**
     * 建構方法
     *
     * @return void
     */
    public function __construct()
    {
        $this->verify_ssl = (bool) config('request.verify_ssl');
        $this->sso_uri = env('SINGLE_SIGN_ON_BACKEND', 'http://localhost');
    }

    /**
     * 驗證登入狀態
     *
     * @param string $access_token 存取權杖
     * @return array<string,mixed>
     *
     * @throws \InvalidArgumentException
     */
    public function verifyAccessTokenValid(string $access_token): array
    {
        $url = $this->sso_uri.'/api/v1/user';
        $headers = [
            'Authorization' => 'Bearer '.$access_token,
        ];

        $client = Http::withHeaders($headers);
        if (! $this->verify_ssl) {
            $client = $client->withoutVerifying();
        }

        $response = $client->get($url);
        $response_status = $response->status();
        if ($response_status < 400) {
            $response = $response->json();
        } elseif ($response_status >= 400 && $response_status < 500) {
            $response = $response->json();
            throw new InvalidArgumentException($response['message']);
        } else {
            throw new InvalidArgumentException('單一登入入口發生錯誤，請稍後再試');
        }

        return $response;
    }
}
