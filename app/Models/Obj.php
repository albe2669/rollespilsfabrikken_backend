<?php

namespace App\Models;

use Dyrynda\Database\Casts\EfficientUuid;
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
        'type'
    ];

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function permissions() {
        return $this->hasMany('App\Models\Permission');
    }

    public function obj() {
        switch ($this->type) {
            case 'forum':
                return $this->hasOne('App\Models\Forum');
                break;

            case 'calendar':
                return $this->hasOne('App\Models\Calendar');
                break;

            default:
                break;
        }
    }
}
