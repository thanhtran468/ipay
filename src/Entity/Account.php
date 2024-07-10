<?php

namespace IPay\Entity;

readonly class AccountState
{
    public function __construct(
        public int $availableBalance,
        public int $balance,
    ) {
    }
}

readonly class Account
{
    public function __construct(
        public string $title,
        public string $number,
        public string $currencyCode,
        public AccountState $accountState,
    ) {
    }
}
