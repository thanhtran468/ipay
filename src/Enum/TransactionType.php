<?php

namespace IPay\Enum;

enum TransactionType: string
{
    case CREDIT = 'Credit';
    case DEBIT = 'Debit';
    case ALL = '';
}
