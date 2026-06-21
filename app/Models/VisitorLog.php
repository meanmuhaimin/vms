<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitorLog extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $primaryKey = 'log_id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'visitor_id',
        'host_employee_id',
        'assigned_location_id',
        'verification_channel',
        'status',
        'checkin_submit_time',
        'desk_release_time',
        'host_approval_time',
        'checkout_time',
    ];

    protected function casts(): array
    {
        return [
            'checkin_submit_time' => 'datetime',
            'desk_release_time' => 'datetime',
            'host_approval_time' => 'datetime',
            'checkout_time' => 'datetime',
        ];
    }

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(Visitor::class, 'visitor_id', 'visitor_id');
    }
}
