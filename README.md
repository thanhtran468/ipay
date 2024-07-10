## IPay Unoffical Client

```php
<?php

declare(strict_types=1);

use IPay\IPayClient;

require __DIR__.'/vendor/autoload.php';

$ipay = IPayClient::create();

try {
    $session = $ipay->guest()->login([
        'userName' => 'yourUsername',
        'accessCode' => 'yourPassword'
    ]);

    foreach ($session->historyTransactions([
        'accountNumber' => $session->customer->accountNumber,
        'startDate' => new \DateTimeImmutable('-5 days'),
    ]) as $transaction) {
        echo $transaction->remark.PHP_EOL;
    }
} catch (Throwable $e) {
    echo $e->getMessage();
}
```
