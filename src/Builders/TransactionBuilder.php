<?php

namespace IPay\Builders;

use IPay\Enums\TransactionType;
use IPay\ValueObjects\Transaction;

/**
 * @psalm-import-type ParametersType from BodyBuilder
 *
 * @implements \IteratorAggregate<int,Transaction>
 */
final class TransactionBuilder implements \IteratorAggregate
{
    /** @var \Closure(ParametersType):\Traversable<Transaction> */
    private \Closure $resolver;

    /** @var ParametersType */
    private array $parameters = [];

    public function between(
        \DateTimeInterface $from,
        \DateTimeInterface $to,
    ): self {
        $this->parameters['startDate'] = $from->format('Y-m-d');
        $this->parameters['endDate'] = $to->format('Y-m-d');

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
        return ($this->resolver)($this->parameters);
    }
}
