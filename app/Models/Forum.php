<?php

namespace App\Models;

use Dyrynda\Database\Support\Casts\EfficientUuid;;
use Dyrynda\Database\Support\GeneratesUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Laravel\Scout\Searchable;

/**
 * Class Forum
 *
 * @property int $id
 * @property string $uuid
 * @property int $obj_id
 * @property string $title
 * @property string $colour
 * @property string $description
 * @property int $priority
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @mixin Builder
 */
class Forum extends Model
{
    use Searchable, GeneratesUuid, HasFactory;

    protected $casts = [
        'uuid' => EfficientUuid::class,
    ];

    protected $fillable = [
        'title',
        'description',
        'colour'
    ];

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function obj() {
        return $this->belongsTo('App\Models\Obj');
    }

    public function toSearchableArray() {
        $array = $this->toArray();

        $array = Arr::only($array, [
            'id',
            'title',
            'description'
        ]);

        return $array;
    }

    public function permissions() {
        return $this->obj()->first()->permissions;
    }

    public function posts() {
        return $this->hasMany('App\Models\Post');
    }

    public function comments() {
        return $this->hasManyThrough('App\Models\Comment', 'App\Models\Post');
    }

    public function getTableColumns() {
        return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
    }
}
