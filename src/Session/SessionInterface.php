<?php

namespace IPay\Session;

interface SessionInterface
{
    /**
     * @return string[]
     */
    public function getRequestParameters(): array;
}
