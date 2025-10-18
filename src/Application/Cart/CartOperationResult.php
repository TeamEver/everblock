<?php

namespace Everblock\Tools\Application\Cart;

final class CartOperationResult
{
    /**
     * @param string[] $errors
     */
    public function __construct(
        private readonly bool $success,
        private readonly string $message,
        private readonly ?string $redirectUrl = null,
        private readonly array $errors = []
    ) {
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getRedirectUrl(): ?string
    {
        return $this->redirectUrl;
    }

    /**
     * @return string[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
