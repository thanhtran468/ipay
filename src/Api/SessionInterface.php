<?php

namespace IPay\Api;

interface SessionInterface
{
    /**
     * @return string[]
     */
    public function getRequestParameters(): array;
}
