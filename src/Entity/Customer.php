<?php

namespace IPay\Entity;

use EventSauce\ObjectHydrator\MapFrom;

#[MapFrom([
    'fullname' => 'name',
    'phone' => 'phone',
    'jobTitle' => 'job',
    'feeAcctNo' => 'accountNumber',
])]
readonly class Customer
{
    public function __construct(
        public string $name,
        public string $phone,
        public string $job,
        public string $accountNumber,
    ) {
    }
}
