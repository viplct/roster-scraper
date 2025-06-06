<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Scout\Searchable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, Searchable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'job_title',
        'phone',
        'verified_at',
        'address',
        'social_urls',
        'bio',
        'expertise',
        'skills',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'social_urls' => 'array',
        ];
    }

    /**
     * Get the works for the user.
     */
    public function works(): HasMany
    {
        return $this->hasMany(Work::class);
    }

    /**
     * Get the clients for the user.
     */
    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    /**
     * Get all client media through clients.
     */
    public function clientMedia(): HasManyThrough
    {
        return $this->hasManyThrough(ClientMedia::class, Client::class);
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        // Load clients relationship for search context
        $this->loadMissing(['clients']);

        return [
            'id' => (string)$this->id,
            'name' => $this->name ?: '',
            'username' => $this->username ?: '',
            'job_title' => $this->job_title ?: '',
            'bio' => $this->bio ?: '',
            'expertise' => $this->expertise ?: '',
            'skills' => $this->skills ?: '',
            'client_job_titles' => $this->clients->pluck('job_title')->filter()->implode(', '),
            'created_at' => $this->created_at->timestamp,
            'updated_at' => $this->updated_at->timestamp,
        ];
    }

    /**
     * Get the name of the index associated with the model.
     */
    public function searchableAs(): string
    {
        return 'users_index';
    }

    /**
     * Modify the query used to retrieve models when making all of the models searchable.
     */
    protected function makeAllSearchableUsing($query)
    {
        return $query->with(['works', 'clients']);
    }
}
