<?php

namespace IPay\Api;

use IPay\Builder\TransactionBuilder;
use IPay\Entity\Account;
use IPay\Entity\Customer;
use IPay\Entity\Transaction;
use IPay\Enum\TransactionType;
use IPay\IPayClient;
use IPay\Session\AuthenticatedSession;
use Symfony\Component\OptionsResolver\Options;
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
        $this->customer = $this->customer();
        $this->accounts = $this->accounts();
    }

    /**
     * @throws \IPay\Exception\SessionException
     */
    private function customer(): Customer
    {
        return $this->objectMapper->hydrateObject(
            Customer::class,
            $this->post('getCustomerDetails')['customerInfo'],
        );
    }

    /**
     * @return list<Account>
     *
     * @throws \IPay\Exception\SessionException
     */
    private function accounts(): array
    {
        return $this->objectMapper->hydrateObjects(
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
        $datetimeNormalizer = fn (
            Options $resolver,
            \DateTimeInterface $value
        ): string => $value->format('Y-m-d');

        $transactionTypeNormalizer = fn (
            Options $resolver,
            TransactionType $value
        ): string => $value->value;

        $resolver = self::createOptionsResolver()
            ->setRequired('accountNumber')
            ->setDefined([
                'tranType',
                'startDate',
                'endDate',
            ])
            ->setAllowedTypes('accountNumber', 'string')
            ->setAllowedTypes('tranType', TransactionType::class)
            ->setAllowedTypes('startDate', \DateTimeInterface::class)
            ->setAllowedTypes('endDate', \DateTimeInterface::class)
            ->setNormalizer('tranType', $transactionTypeNormalizer)
            ->setNormalizer('startDate', $datetimeNormalizer)
            ->setNormalizer('endDate', $datetimeNormalizer)
        ;

        $parameters = $resolver->resolve($parameters);

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
