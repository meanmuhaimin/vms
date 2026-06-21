<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WayfindingLocation extends Model
{
    protected $primaryKey = 'location_id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'location_id',
        'display_name',
        'building_sector',
        'floor_label',
        'map_asset_path',
        'route_steps',
        'is_public',
    ];

    protected function casts(): array
    {
        return [
            'route_steps' => 'array',
            'is_public' => 'boolean',
        ];
    }
}
