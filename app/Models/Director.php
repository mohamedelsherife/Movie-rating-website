<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Director extends Model
{
    protected $table = 'directors';

    protected $fillable = [
        'name',
    ];

    public function movies()
    {
        return $this->hasMany(Movie::class);
    }

    public function series()
    {
        return $this->hasMany(Series::class);
    }

    /**
     * Return the director with this name, creating it only if it does not exist yet.
     * The match ignores letter case and extra spaces, so "christopher  nolan"
     * reuses "Christopher Nolan" instead of creating a duplicate.
     */
    public static function findOrCreateByName(string $name): self
    {
        $name = Str::squish($name);

        return static::whereRaw('LOWER(name) = ?', [Str::lower($name)])->first()
            ?? static::create(['name' => $name]);
    }
}
