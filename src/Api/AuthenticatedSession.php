<?php

namespace IPay\Api;

final class AuthenticatedSession extends UnauthenticatedSession
{
    public function __construct(public string $id)
    {
    }

    public function getRequestParameters(): array
    {
        return ['sessionId' => $this->id, ...parent::getRequestParameters()];
    }
}
