<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpLedger extends Model
{
    public $timestamps = false;

    protected $table = 'otp_ledger';

    protected $primaryKey = 'otp_id';

    protected $fillable = [
        'phone_number',
        'otp_token_hash',
        'expiration_time',
        'attempts_count',
    ];

    protected function casts(): array
    {
        return [
            'expiration_time' => 'datetime',
            'attempts_count' => 'integer',
        ];
    }
}
