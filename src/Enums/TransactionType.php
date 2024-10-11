<?php

namespace IPay\Enums;

enum TransactionType: string
{
    case CREDIT = 'Credit';
    case DEBIT = 'Debit';
    case ALL = '';
}
