## IPay Unoffical Client

[![static analysis](https://github.com/thanhtran468/ipay/actions/workflows/static-analysis.yaml/badge.svg)](https://github.com/thanhtran468/ipay/actions/workflows/static-analysis.yaml)
[![tests](https://github.com/thanhtran468/ipay/actions/workflows/tests.yaml/badge.svg)](https://github.com/thanhtran468/ipay/actions/workflows/tests.yaml)

```php
<?php

declare(strict_types=1);

use IPay\Enums\TransactionType;
use IPay\IPayClient;

require __DIR__.'/vendor/autoload.php';

try {
    $session = IPayClient::fromCredentials('username', 'password');

    # Lịch sử giao dịch hôm nay
    $transactions = $session->transactions()
        ->type(TransactionType::CREDIT)
        ->today();

    foreach ($transactions as $transaction) {
        echo $transaction->remark.PHP_EOL;
    }
} catch (Throwable $e) {
    echo $e->getMessage();
}
```
