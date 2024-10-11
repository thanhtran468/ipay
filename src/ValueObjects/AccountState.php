<?php

namespace IPay\ValueObjects;

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
