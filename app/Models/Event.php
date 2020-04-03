<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Laravel\Scout\Searchable;
use App\Events\Event\EventSaved;

/**
 * Class Event
 * @mixin Builder
 */
class Event extends Model
{
    use Searchable;

    protected $fillable = [
        'title',
        'description',
        'start',
        'end',
        'recurrence'
    ];

    public function toSearchableArray() {
        $array = $this->toArray();

        $array = Arr::only($array, [
            'id',
            'title',
            'description',
            'start',
            'end'
        ]);

        return $array;
    }

    public function calendar() {
        return $this->belongsTo('App\Models\Calendar');
    }

    public function getTableColumns() {
        return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
    }

    public function user() {
        return $this->belongsTo('App\Models\User');
    }

    public function events()  {
        return $this->hasMany(Event::class, 'event_id', 'id');
    }

    public function event() {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function saveQuietly() {
        return static::withoutEvents(function () {
            return $this->save();
        });
    }
}
