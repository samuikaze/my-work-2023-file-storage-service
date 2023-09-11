<?php

namespace App\Http\Middleware;

use App\Services\RequestService;
use App\Traits\ResponseFormatterTrait;
use Closure;
use Illuminate\Http\Request;
use InvalidArgumentException;

class VerifyAuthentication
{
    use ResponseFormatterTrait;

    public const HTTP_BAD_REQUEST = 400;
    public const HTTP_UNAUTHORIZED = 401;
    public const HTTP_FORBIDDEN = 403;

    /**
     * RequestService
     *
     * @var \App\Services\RequestService
     */
    protected $request_service;

    /**
     * 建構方法
     *
     * @param \App\Services\RequestService $request_service
     * @return void
     */
    public function __construct(RequestService $request_service)
    {
        $this->request_service = $request_service;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $access_token = $request->bearerToken();
        if (is_null($access_token)) {
            return $this->response('Unauthorized', null, self::HTTP_UNAUTHORIZED);
        }

        try {
            [
                'status' => $status,
                'message' => $error,
                'data' => $data,
            ] = $this->request_service->verifyAccessTokenValid($access_token);
        } catch (InvalidArgumentException $e) {
            return $this->response($e->getMessage(), null, self::HTTP_UNAUTHORIZED);
        }

        // 是要不要儲存 Log、登入資訊等等
        $request->merge([
            'authorization' => $data,
        ]);

        return $next($request);
    }
}
