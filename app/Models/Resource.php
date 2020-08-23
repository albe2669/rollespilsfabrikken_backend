<?php

namespace App\Models;

use Dyrynda\Database\Casts\EfficientUuid;
use Dyrynda\Database\Support\GeneratesUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Class Resource
 * Handles rooms and equipment
 *
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property string $description
 * @property string $type enum(room, equipment)
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @mixin Builder
 */
class Resource extends Model
{
    use GeneratesUuid;

    protected $casts = [
        'uuid' => EfficientUuid::class,
    ];

    protected $fillable = [
        'name',
        'description',
        'type'
    ];

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function toSearchableArray() {
        return Arr::only($this->toArray(), [
            'uuid',
            'name',
            'description',
            'type'
        ]);
    }

    public function events() {
        return $this->belongsToMany('App\Models\Event', 'event_resources');
    }

    public function getTableColumns() {
        return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
    }
}
