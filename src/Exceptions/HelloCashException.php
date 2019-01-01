<?php

namespace drsdre\HelloCash\Exceptions;

use Exception;

class HelloCashException extends Exception
{
	const BALANCE_TOO_LOW = 0;
	const DAILY_LIMIT_REACHED = 1;
	const MAX_CREDIT_LIMIT_REACHED = 2;
	const ACCOUNT_SUSPENDED = 3;
	const ACCOUNT_NOT_FOUND = 4;
	const AMOUNT_TOO_LOW = 5;
	const AMOUNT_TOO_HIGH = 6;
	const AMOUNT_NOT_AVAILABLE = 7;
	const AMOUNT_INVALID = 8;
	const NOT_ENOUGH_CREDITS = 9;
	const RECIPIENT_REJECTED = 10;
	const RECEIVER_UPPER_CREDIT_LIMIT_REACHED = 11;
	const TRACENUMBER_NOT_UNIQUE = 12;
	const NO_POSSIBLE_TRANSFER_TYPES = 13;
	const FROM_NOT_FOUND = 14;
	const TO_NOT_FOUND = 15;
	const EXPIRES_TOO_EARLY = 16;
	const EXPIRES_TOO_LATE = 17;
	const NO_FROM_SELF_TRANSFER = 18;
	const NO_TO_SELF_TRANSFER = 19;
	const TRANSFER_ALREADY_PROCESSED = 20;
	const TRANSFER_NOT_MATCHED = 21;
	const REFERENCEID_NOT_UNIQUE = 22;

    public function __construct($message, $code)
    {
        parent::__construct("Error {$code}': {$message}", $code);
    }
}
