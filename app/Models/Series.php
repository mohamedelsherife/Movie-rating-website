<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Series extends Model
{
    protected $table = 'series';

    protected $fillable = [
        'title',
        'director_id',
        'start_year',
        'end_year',
        'seasons',
        'episodes',
        'description',
        'poster',
    ];

    protected $casts = [
        'start_year' => 'integer',
        'end_year'   => 'integer',
        'seasons'    => 'integer',
        'episodes'   => 'integer',
    ];

    public function director()
    {
        return $this->belongsTo(Director::class);
    }

    public function genres()
    {
        return $this->belongsToMany(Genre::class, 'series_genres')->withTimestamps();
    }

    /** A series without an end year is still running. */
    public function isOngoing(): bool
    {
        return $this->end_year === null;
    }

    /** "2008 – 2013", "2019 – Present", or a single year for one-year runs. */
    public function yearsLabel(): string
    {
        if ($this->isOngoing()) {
            return $this->start_year . ' – Present';
        }

        return $this->start_year === $this->end_year
            ? (string) $this->start_year
            : $this->start_year . ' – ' . $this->end_year;
    }
}
