<?php

namespace App\Models;

use Dyrynda\Database\Support\Casts\EfficientUuid;
use Dyrynda\Database\Support\GeneratesUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Laravel\Scout\Searchable;

/**
 * Class Event
 *
 * @property int $id
 * @property string $uuid
 * @property int $calendar_id
 * @property int $user_id
 * @property int $series_id
 * @property string $title
 * @property string $description
 * @property string $start
 * @property int $event_length
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @mixin Builder
 */
class Event extends Model
{
    use GeneratesUuid;
    use HasFactory;
    use Searchable;

    protected $casts = [
        'uuid' => EfficientUuid::class,
    ];

    protected $fillable = [
        'title',
        'description',
        'start',
        'event_length',
    ];

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function toSearchableArray()
    {
        $array = $this->toArray();

        $array = Arr::only($array, [
            'id',
            'title',
            'description',
            'start',
            'end',
        ]);

        return $array;
    }

    public function series()
    {
        return $this->belongsTo(EventSerie::class);
    }

    public function calendar()
    {
        return $this->belongsTo(Calendar::class);
    }

    public function getTableColumns()
    {
        return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function meta()
    {
        return $this->hasOne(EventMeta::class);
    }

    public function resources()
    {
        return $this->hasManyThrough(Resource::class, EventResource::class, 'event_id', 'id', 'id', 'resource_id');
    }
}
