<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Show extends Model
{
    use HasUuids;

    protected $table = 'shows';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id_integration',
        'name',
        'type',
        'language',
        'status',
        'runtime',
        'average_runtime',
        'official_site',
        'rating',
        'summary',
    ];

    protected $casts = [
        'id_integration' => 'integer',
        'runtime' => 'integer',
        'average_runtime' => 'integer',
        'rating' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function episodes(): HasMany
    {
        return $this->hasMany(Episode::class);
    }
}
