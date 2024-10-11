<?php

namespace IPay\ValueObjects;

use EventSauce\ObjectHydrator\Constructor;

readonly class Account
{
    private function __construct(
        public string $title,
        public string $number,
        public string $currencyCode,
        public AccountState $accountState,
    ) {
    }

    #[Constructor]
    public static function create(
        string $title,
        string $number,
        string $currencyCode,
        AccountState $accountState,
    ): self {
        return new self(
            $title,
            $number,
            $currencyCode,
            $accountState,
        );
    }
}
