## IPay Unoffical Client

```php
<?php

declare(strict_types=1);

use IPay\Enum\TransactionType;
use IPay\IPayClient;

require __DIR__.'/vendor/autoload.php';

$ipay = IPayClient::create();

try {
    $session = $ipay->login(
        userName: 'username',
        accessCode: 'password',
    );

    $transactions = $session->transactions()
        ->type(TransactionType::CREDIT)
        ->today()
        ->getIterator();

    foreach ($transactions as $transaction) {
        echo $transaction->remark.PHP_EOL;
    }
} catch (Throwable $e) {
    echo $e->getMessage();
}
```
