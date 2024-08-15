<?php

namespace IPay\Api;

use IPay\Builder\TransactionBuilder;
use IPay\Entity\Account;
use IPay\Entity\Customer;
use IPay\Entity\Transaction;
use IPay\IPayClient;
use IPay\Session\AuthenticatedSession;
use Symfony\Component\VarExporter\LazyGhostTrait;

/**
 * @psalm-import-type ParametersType from TransactionBuilder
 *
 * @extends AbstractApi<AuthenticatedSession>
 */
final class AuthenticatedApi extends AbstractApi
{
    use LazyGhostTrait;

    public Customer $customer;

    /** @var list<Account> */
    public array $accounts;

    public function __construct(
        IPayClient $iPayClient,
        AuthenticatedSession $session,
    ) {
        parent::__construct($iPayClient, $session);
        self::createLazyGhost(
            initializer: $this->populateLazyProperties(...),
            instance: $this,
        );
    }

    private function populateLazyProperties(): void
    {
        $this->customer = $this->objectMapper->hydrateObject(
            Customer::class,
            $this->post('getCustomerDetails')['customerInfo'],
        );
        $this->accounts = $this->objectMapper->hydrateObjects(
            Account::class,
            $this->post('getEntitiesAndAccounts')['accounts'],
        )->toArray();
    }

    public function transactions(?string $accountNumber = null): TransactionBuilder
    {
        return new TransactionBuilder(
            ['accountNumber' => $accountNumber ?? $this->customer->accountNumber],
            $this->getTransactions(...),
        );
    }

    /**
     * @param ParametersType $parameters
     *
     * @return \Traversable<int, Transaction>
     *
     * @throws \IPay\Exception\SessionException
     */
    private function getTransactions(array $parameters): \Traversable
    {
        $parameters['pageNumber'] = 0;
        do {
            $transactions = $this->post(
                'getHistTransactions',
                $parameters
            )['transactions'];
            foreach ($this->objectMapper->hydrateObjects(
                Transaction::class,
                $transactions
            )->getIterator() as $transaction) {
                yield $transaction;
            }
            ++$parameters['pageNumber'];
        } while (count($transactions) > 0);
    }
}
