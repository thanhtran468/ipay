<?php

namespace IPay\Builder;

use IPay\Entity\Transaction;
use IPay\Enum\TransactionType;

/**
 * @psalm-import-type ParametersType from BodyBuilder
 *
 * @psalm-type GetterType = \Closure(ParametersType):\Traversable<Transaction>
 *
 * @implements \IteratorAggregate<int,Transaction>
 */
final class TransactionBuilder implements \IteratorAggregate
{
    /**
     * @param GetterType     $getter
     * @param ParametersType $parameters
     */
    private function __construct(
        private \Closure $getter,
        private array $parameters,
    ) {
    }

    /**
     * @param GetterType     $getter
     * @param ParametersType $parameters
     */
    public static function from(
        \Closure $getter,
        array $parameters,
    ): self {
        return new self(
            $getter,
            $parameters,
        );
    }

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
        return ($this->getter)($this->parameters);
    }
}
