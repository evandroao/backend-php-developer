<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Episode extends Model
{
    use HasUuids;

    protected $table = 'episodes';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id_integration',
        'show_id',
        'name',
        'season',
        'number',
        'type',
        'airdate',
        'airtime',
        'airstamp',
        'runtime',
        'rating',
        'summary',
    ];

    protected $casts = [
        'id_integration' => 'integer',
        'season' => 'integer',
        'number' => 'integer',
        'runtime' => 'integer',
        'rating' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function show(): BelongsTo
    {
        return $this->belongsTo(Show::class);
    }
}
