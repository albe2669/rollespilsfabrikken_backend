<?php

namespace App\Models;

use Dyrynda\Database\Support\Casts\EfficientUuid;;
use Dyrynda\Database\Support\GeneratesUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Laravel\Scout\Searchable;

/**
 * Class Permission
 *
 * @property int $id
 * @property string $uuid
 * @property int $obj_id
 * @property int $level
 * @property string $title
 * @property string $description
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @mixin Builder
 */
class Permission extends Model
{
    use Searchable, GeneratesUuid;

    protected $casts = [
        'uuid' => EfficientUuid::class,
    ];

    protected $fillable = [
        'obj_id',
        'level',
        'title',
        'description'
    ];

    public function getRouteKeyName()
    {
        return 'uuid';
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

    public function getTableColumns() {
        return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
    }

    public function obj() {
        return $this->belongsTo('App\Models\Obj');
    }

    public function roles() {
        return $this->belongsToMany('App\Models\Role', 'role_perms');
    }
}
