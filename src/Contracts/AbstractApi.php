<?php

namespace IPay\Contracts;

use IPay\Builders\TransactionBuilder;
use IPay\ValueObjects\Account;
use IPay\ValueObjects\Customer;

abstract class AbstractApi
{
    public Customer $customer;

    /** @var list<Account> */
    public array $accounts;

    abstract public function transactions(?string $accountNumber = null): TransactionBuilder;
}
