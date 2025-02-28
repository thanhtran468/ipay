<?php

namespace IPay\Contracts;

use IPay\Resources\Transactions;
use IPay\ValueObjects\Account;
use IPay\ValueObjects\Customer;

/**
 * @phpstan-type ParametersType = array<string, int|string>
 */
abstract class AbstractApi
{
    public Customer $customer;

    /** @var list<Account> */
    public array $accounts;

    abstract public function transactions(?string $accountNumber = null): Transactions;
}
