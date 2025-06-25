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
 * Class Role
 *
 * @property int $id
 * @property string $uuid
 * @property string $title
 * @property string $color
 * @property bool $show
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @mixin Builder
 */
class Role extends Model
{
    use GeneratesUuid;
    use HasFactory;
    use Searchable;

    protected $casts = [
        'uuid' => EfficientUuid::class,
        'show' => 'bool',
    ];

    protected $fillable = [
        'title',
        'color',
        'show',
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
        ]);

        return $array;
    }

    public function permissions()
    {
        return $this->hasManyThrough(Permission::class, RolePerm::class, 'role_id', 'id', 'id', 'permission_id');
    }
}
