<?php

namespace IPay\ValueObjects;

use EventSauce\ObjectHydrator\Constructor;

readonly class Customer
{
    private function __construct(
        public string $name,
        public string $phone,
        public string $job,
        public string $accountNumber,
    ) {
    }

    #[Constructor]
    public static function create(
        string $fullname,
        string $phone,
        string $jobTitle,
        string $feeAcctNo,
    ): self {
        return new self(
            $fullname,
            $phone,
            $jobTitle,
            $feeAcctNo,
        );
    }
}
