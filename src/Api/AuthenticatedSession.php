<?php

namespace IPay\Api;

final class AuthenticatedSession extends UnauthenticatedSession
{
    public function __construct(private string $sessionId)
    {
    }

    public function getRequestParameters(): array
    {
        return get_object_vars($this) + parent::getRequestParameters();
    }

    public function getSessionId(): string
    {
        return $this->sessionId;
    }
}
