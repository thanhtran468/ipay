<?php

namespace IPay\Api;

use IPay\Entity\Customer;
use IPay\IPayClient;

final class Session extends AbstractApi
{
    public function __construct(
        private string $id,
        protected IPayClient $client,
    ) {
        parent::__construct($client);
    }

    public function customer(): Customer
    {
        return $this->getObjectMapper()->hydrateObject(
            Customer::class,
            $this->post('/getCustomerDetails'),
        );
    }

    protected function configureFieldBuilder(FieldBuilder $builder): FieldBuilder
    {
        $builder['sessionId'] = $this->id;

        return parent::configureFieldBuilder($builder);
    }
}
