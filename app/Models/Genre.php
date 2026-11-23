<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Genre extends Model
{
    protected $table = 'genres';

    protected $fillable = [
        'name',
    ];

    public function movies()
    {
        return $this->belongsToMany(Movie::class, 'movie_genres')->withTimestamps();
    }

    /**
     * Return the genre with this name, creating it only if it does not exist yet
     * (case-insensitive, extra spaces ignored).
     */
    public static function findOrCreateByName(string $name): self
    {
        $name = Str::squish($name);

        return static::whereRaw('LOWER(name) = ?', [Str::lower($name)])->first()
            ?? static::create(['name' => $name]);
    }
}
