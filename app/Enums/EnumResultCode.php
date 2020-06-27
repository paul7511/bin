<?php

namespace App\Enums;

use MabeEnum\Enum;

/**
 * Success.
 *
 * @method static EnumResultCode SUCCESS()
 *
 * Base
 * @method static EnumResultCode INVALID_INPUT()
 * @method static EnumResultCode INVALID_HEADER()
 * @method static EnumResultCode DB_ERROR()
 * @method static EnumResultCode UNHANDLED_ERROR()
 *
 */
class EnumResultCode extends Enum
{
    const SUCCESS = '0000';

    // CBXX
    const INVALID_INPUT = 'CB01';
    const INVALID_HEADER = 'CB02';
    const DB_ERROR = 'CB03';
    const UNHANDLED_ERROR = '9999';

    protected $DESCRIPTION = [
        self::SUCCESS => 'Success',

        self::INVALID_INPUT => 'Input Invalid',
        self::INVALID_HEADER => 'Header Invalid',
        self::DB_ERROR => 'DB Error',
        self::UNHANDLED_ERROR => 'System Unhandled Error',
    ];

    public function getDesc(): string
    {
        return $this->DESCRIPTION[$this->getValue()];
    }
}
