<?php

namespace IPay\Entity;

use EventSauce\ObjectHydrator\PropertyCasters\CastToDateTimeImmutable;
use EventSauce\ObjectHydrator\PropertyCasters\CastToType;

readonly class Transaction
{
    public function __construct(
        public string $currency,
        #[CastToType('integer')]
        public int $amount,
        public string $remark,
        public string $corresponsiveAccount,
        public string $corresponsiveName,
        #[CastToDateTimeImmutable('d-m-Y H:i:s')]
        public \DateTimeImmutable $processDate,
    ) {
    }
}
