<?php

namespace IPay\Builder;

use IPay\Api\AuthenticatedApi;
use IPay\Enum\TransactionType;

/**
 * @psalm-type ParametersType = array{
 *      accountNumber: string,
 *      tranType?: value-of<TransactionType>,
 *      startDate?: \DateTimeInterface,
 *      endDate?: \DateTimeInterface,
 * }
 *
 * @implements \IteratorAggregate<int,\IPay\Entity\Transaction>
 */
final class TransactionBuilder implements \IteratorAggregate
{
    /**
     * @param ParametersType $parameters
     */
    public function __construct(
        private array $parameters,
        private AuthenticatedApi $api,
    ) {
    }

    public function between(
        \DateTimeInterface $from,
        \DateTimeInterface $to,
    ): self {
        $this->parameters['startDate'] = $from;
        $this->parameters['endDate'] = $to;

        return $this;
    }

    public function today(): self
    {
        $today = new \DateTimeImmutable();
        $this->between($today, $today);

        return $this;
    }

    public function type(TransactionType $type): self
    {
        $this->parameters['tranType'] = $type->value;

        return $this;
    }

    public function getIterator(): \Traversable
    {
        return $this->api->historyTransactions($this->parameters);
    }
}
