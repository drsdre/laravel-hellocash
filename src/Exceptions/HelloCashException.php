<?php

namespace drsdre\HelloCash\Exceptions;

use Exception;
use Illuminate\Support\Arr;

class HelloCashException extends Exception {

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

    // Additional error codes
    const SERVER_ERROR = 30;
    const WRONG_CREDENTIALS = 31;
    const CLIENT_EXCEPTION = 32;
    const NETWORK_UNAVAILABLE = 40;

    const EXCEPTION_EXPLANATIONS = [
        self::BALANCE_TOO_LOW                     => 'Balance too low',
        self::DAILY_LIMIT_REACHED                 => 'Daily limit reached',
        self::MAX_CREDIT_LIMIT_REACHED            => 'Max credit limit reached',
        self::ACCOUNT_SUSPENDED                   => 'Account suspended',
        self::ACCOUNT_NOT_FOUND                   => 'Account not found',
        self::AMOUNT_TOO_LOW                      => 'Amount too low',
        self::AMOUNT_TOO_HIGH                     => 'Amount too high',
        self::AMOUNT_NOT_AVAILABLE                => 'Amount not available',
        self::AMOUNT_INVALID                      => 'Amount invalid',
        self::NOT_ENOUGH_CREDITS                  => 'Not enough credits',
        self::RECIPIENT_REJECTED                  => 'Recipient rejected',
        self::RECEIVER_UPPER_CREDIT_LIMIT_REACHED => 'Receiver upper credit limit reached',
        self::TRACENUMBER_NOT_UNIQUE              => 'Tracenumber not unique',
        self::NO_POSSIBLE_TRANSFER_TYPES          => 'No possible transfer types',
        self::FROM_NOT_FOUND                      => 'From not found',
        self::TO_NOT_FOUND                        => 'To not found',
        self::EXPIRES_TOO_EARLY                   => 'Expires too early',
        self::EXPIRES_TOO_LATE                    => 'Expires too late',
        self::NO_FROM_SELF_TRANSFER               => 'No from self transfer',
        self::NO_TO_SELF_TRANSFER                 => 'No to self transfer',
        self::TRANSFER_ALREADY_PROCESSED          => 'Transfer already processed',
        self::TRANSFER_NOT_MATCHED                => 'Transfer not matched',
        self::REFERENCEID_NOT_UNIQUE              => 'ReferenceId not unique',

        self::SERVER_ERROR        => 'Server error (500)',
        self::WRONG_CREDENTIALS   => 'Wrong credentials',
        self::CLIENT_EXCEPTION    => 'Client data error',
        self::NETWORK_UNAVAILABLE => 'Network fault',
    ];

    /** @var bool */
    public $recoverable = true;

    public function __construct( string $message, int $code, bool $recoverable = true ) {
        $this->recoverable = $recoverable;

        parent::__construct( "Error {$code}: {$message}", $code );
    }

    final public function is_recoverable(): bool {
        return $this->recoverable;
    }

    final public function code_explanation(): string {
        return Arr::get(HelloCashException::EXCEPTION_EXPLANATIONS, $this->getCode(), 'Unknown code ' . $this->getCode() );
    }
}
