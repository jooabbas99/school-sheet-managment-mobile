<?php

namespace App\Exceptions;

use Illuminate\Http\Client\Response;
use RuntimeException;

/**
 * An error returned by the backend API: {"message", "errors", "code"} with an Arabic message.
 */
class ApiException extends RuntimeException
{
    /**
     * Final so fromResponse() can safely build subclasses with `new static`.
     *
     * @param  array<string, list<string>>  $errors
     */
    final public function __construct(
        string $message,
        public readonly int $status = 0,
        public readonly string $errorCode = 'error',
        public readonly array $errors = [],
    ) {
        parent::__construct($message);
    }

    public static function fromResponse(Response $response): static
    {
        $body = $response->json();
        $body = is_array($body) ? $body : [];

        $message = is_string($body['message'] ?? null) && $body['message'] !== ''
            ? $body['message']
            : __('errors.server');

        return new static(
            $message,
            $response->status(),
            is_string($body['code'] ?? null) ? $body['code'] : 'http_'.$response->status(),
            is_array($body['errors'] ?? null) ? $body['errors'] : [],
        );
    }

    public function isValidation(): bool
    {
        return $this->status === 422 && $this->errors !== [];
    }
}
