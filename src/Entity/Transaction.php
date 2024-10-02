<?php

namespace IPay\Entity;

use EventSauce\ObjectHydrator\Constructor;
use EventSauce\ObjectHydrator\PropertyCasters\CastToDateTimeImmutable;
use EventSauce\ObjectHydrator\PropertyCasters\CastToType;

readonly class Transaction
{
    private function __construct(
        public string $currency,
        public int $amount,
        public string $remark,
        public string $corresponsiveAccount,
        public string $corresponsiveName,
        public \DateTimeImmutable $processDate,
    ) {
    }

    #[Constructor]
    public static function create(
        string $currency,
        #[CastToType('integer')]
        int $amount,
        string $remark,
        string $corresponsiveAccount,
        string $corresponsiveName,
        #[CastToDateTimeImmutable('d-m-Y H:i:s')]
        \DateTimeImmutable $processDate,
    ): self {
        return new self(
            $currency,
            $amount,
            $remark,
            $corresponsiveAccount,
            $corresponsiveName,
            $processDate,
        );
    }
}
