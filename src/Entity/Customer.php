<?php

namespace IPay\Entity;

use League\ObjectMapper\MapFrom;

#[MapFrom([
    'fullname' => 'name',
    'phone' => 'phone',
    'jobTitle' => 'job',
    'feeAcctNo' => 'accountNumber',
])]
readonly class Info
{
    /**
     * @internal
     */
    public function __construct(
        public string $name,
        public string $phone,
        public string $job,
        public string $accountNumber
    ) {
    }
}

readonly class Customer
{
    /**
     * @internal
     */
    public function __construct(
        public string $userName,
        #[MapFrom('customerInfo')]
        public Info $info,
    ) {
    }
}
