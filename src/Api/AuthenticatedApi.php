<?php

namespace IPay\Api;

use IPay\Entity\Account;
use IPay\Entity\Customer;
use IPay\Entity\Transaction;
use Symfony\Component\OptionsResolver\Options;

/**
 * @extends AbstractApi<AuthenticatedSession>
 */
class AuthenticatedApi extends AbstractApi
{
    public function customer(): Customer
    {
        return $this->objectMapper->hydrateObject(
            Customer::class,
            $this->post('/getCustomerDetails')['customerInfo'],
        );
    }

    /**
     * @return list<Account>
     */
    public function accounts(): array
    {
        return $this->objectMapper->hydrateObjects(
            Account::class,
            $this->post('/getEntitiesAndAccounts')['accounts'],
        )->toArray();
    }

    /**
     * @param array{
     *      accountNumber: string,
     *      tranType?: 'Credit'|'Debit'|'',
     *      startDate?: \DateTimeInterface,
     *      endDate?: \DateTimeInterface,
     * } $parameters
     *
     * @return \Iterator<int, Transaction>
     */
    public function historyTransactions(array $parameters): \Iterator
    {
        $datetimeNormalizer = static function (
            Options $resolver,
            \DateTimeInterface $value
        ): string {
            return $value->format('Y-m-d');
        };

        $resolver = self::createOptionsResolver()
            ->setRequired([
                'accountNumber',
                'tranType',
                'startDate',
                'endDate',
            ])
            ->setAllowedTypes('accountNumber', 'string')
            ->setAllowedValues('tranType', ['Credit', 'Debit', ''])
            ->setAllowedTypes('startDate', \DateTimeInterface::class)
            ->setAllowedTypes('endDate', \DateTimeInterface::class)
            ->setNormalizer('startDate', $datetimeNormalizer)
            ->setNormalizer('endDate', $datetimeNormalizer)
            ->setDefaults([
                'tranType' => 'Credit',
                'startDate' => new \DateTimeImmutable(),
                'endDate' => new \DateTimeImmutable(),
            ])
        ;

        $parameters = $resolver->resolve($parameters);

        $parameters['pageNumber'] = 0;
        do {
            $transactions = $this->post(
                '/getHistTransactions',
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
