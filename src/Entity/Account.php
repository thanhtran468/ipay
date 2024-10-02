<?php

namespace IPay\Entity;

use EventSauce\ObjectHydrator\Constructor;

readonly class AccountState
{
    private function __construct(
        public int $availableBalance,
        public int $balance,
    ) {
    }

    #[Constructor]
    public static function create(
        int $availableBalance,
        int $balance,
    ): self {
        return new self(
            $availableBalance,
            $balance,
        );
    }
}

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
