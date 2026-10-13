<?php

namespace App\Services\Api;

use App\Exceptions\ApiException;
use App\Exceptions\OfflineException;
use App\Exceptions\UnauthenticatedException;
use App\Services\Auth\TokenStore;
use App\Services\Connectivity;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * The only place that talks HTTP to the backend. Feature services (AuthApi, StudentsApi, ...) build on it.
 *
 * Errors become exceptions: OfflineException (no connection), UnauthenticatedException (401, token cleared),
 * ApiException (any other error, with the server's Arabic message and field errors).
 */
class ApiClient
{
    public function __construct(
        private readonly TokenStore $tokens,
        private readonly Connectivity $connectivity,
    ) {}

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public function get(string $uri, array $query = []): array
    {
        return $this->send(fn (PendingRequest $http) => $http->get($uri, $query));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function post(string $uri, array $data = []): array
    {
        return $this->send(fn (PendingRequest $http) => $http->post($uri, $data));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function put(string $uri, array $data = []): array
    {
        return $this->send(fn (PendingRequest $http) => $http->put($uri, $data));
    }

    /**
     * @return array<string, mixed>
     */
    public function delete(string $uri): array
    {
        return $this->send(fn (PendingRequest $http) => $http->delete($uri));
    }

    /**
     * Multipart upload (e.g. an Excel file).
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function upload(string $uri, string $field, string $path, string $fileName, array $data = []): array
    {
        return $this->send(fn (PendingRequest $http) => $http
            ->attach($field, (string) file_get_contents($path), $fileName)
            ->post($uri, array_filter($data, fn ($value) => $value !== null)), json: false);
    }

    /**
     * Download a file from the API (with the token) and return its bytes.
     *
     * @param  array<string, mixed>  $query
     */
    public function download(string $uri, array $query = []): string
    {
        return $this->sendRaw(fn (PendingRequest $http) => $http->accept('*/*')->get($uri, $query))->body();
    }

    /**
     * Download a signed link returned by the API (absolute URL, no token needed).
     */
    public function downloadSigned(string $url): string
    {
        return $this->sendRaw(fn () => Http::timeout(config('api.timeout'))
            ->connectTimeout(config('api.connect_timeout'))
            ->get($url))->body();
    }

    /**
     * @param  callable(PendingRequest): Response  $call
     * @return array<string, mixed>
     */
    private function send(callable $call, bool $json = true): array
    {
        $json = $this->sendRaw($call, $json)->json();

        return is_array($json) ? $json : [];
    }

    /**
     * @param  callable(PendingRequest): Response  $call
     */
    private function sendRaw(callable $call, bool $json = true): Response
    {
        if ($this->connectivity->isOffline()) {
            throw new OfflineException;
        }

        try {
            $response = $call($this->request($json));
        } catch (ConnectionException) {
            throw new OfflineException;
        }

        if ($response->status() === 401) {
            $this->tokens->clear();

            throw UnauthenticatedException::fromResponse($response);
        }

        if ($response->failed()) {
            // A deactivated account is signed out on the server; do the same here.
            if ($response->json('code') === 'inactive_user') {
                $this->tokens->clear();

                throw UnauthenticatedException::fromResponse($response);
            }

            throw ApiException::fromResponse($response);
        }

        return $response;
    }

    /**
     * @param  bool  $json  false for multipart uploads (a JSON Content-Type header would override multipart)
     */
    private function request(bool $json = true): PendingRequest
    {
        $request = Http::baseUrl(config('api.base_url'))
            ->acceptJson()
            ->when($json, fn (PendingRequest $r) => $r->asJson())
            ->timeout(config('api.timeout'))
            ->connectTimeout(config('api.connect_timeout'));

        $token = $this->tokens->get();

        return $token ? $request->withToken($token) : $request;
    }
}
