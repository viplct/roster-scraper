<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientMedia extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'client_id',
        'url',
        'type',
        'title',
        'description'
    ];

    /**
     * Get the client that owns the media.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
} 