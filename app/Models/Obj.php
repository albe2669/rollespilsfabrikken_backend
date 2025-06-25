<?php

namespace App\Models;

use Dyrynda\Database\Support\Casts\EfficientUuid;
use Dyrynda\Database\Support\GeneratesUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Class Obj
 *
 * @property int $id
 * @property string $uuid
 * @property string $type
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @mixin Builder
 */
class Obj extends Model
{
    use GeneratesUuid;

    protected $casts = [
        'uuid' => EfficientUuid::class,
    ];

    protected $fillable = [
        'type',
    ];

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function permissions()
    {
        return $this->hasMany(Permission::class);
    }

    public function obj()
    {
        switch ($this->type) {
            case 'forum':
                return $this->hasOne(Forum::class);
                break;

            case 'calendar':
                return $this->hasOne(Calendar::class);
                break;

            default:
                break;
        }
    }
}
