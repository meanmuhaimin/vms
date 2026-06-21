<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Visitor extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $primaryKey = 'visitor_id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'phone_number',
        'email_address',
        'full_name',
        'company_name',
        'id_doc_type',
        'encrypted_id_number',
    ];

    protected $hidden = [
        'encrypted_id_number',
    ];

    protected function casts(): array
    {
        return [
            'encrypted_id_number' => 'encrypted',
            'created_at' => 'datetime',
        ];
    }

    public function logs(): HasMany
    {
        return $this->hasMany(VisitorLog::class, 'visitor_id', 'visitor_id');
    }
}
