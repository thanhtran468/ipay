<?php

namespace IPay\Builder;

use IPay\Entity\Transaction;
use IPay\Enum\TransactionType;

/**
 * @psalm-type ParametersType = array{
 *      accountNumber: string,
 *      tranType?: TransactionType,
 *      startDate?: \DateTimeInterface,
 *      endDate?: \DateTimeInterface,
 * }
 *
 * @implements \IteratorAggregate<int,Transaction>
 */
final class TransactionBuilder implements \IteratorAggregate
{
    /**
     * @param ParametersType                                     $parameters
     * @param \Closure(ParametersType):\Traversable<Transaction> $getter
     */
    public function __construct(
        private array $parameters,
        private \Closure $getter,
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
        $this->parameters['tranType'] = $type;

        return $this;
    }

    public function getIterator(): \Traversable
    {
        return ($this->getter)($this->parameters);
    }
}
