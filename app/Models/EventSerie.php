<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Class EventSerie
 *
 * @property int $id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @mixin Builder
 */
class EventSerie extends Model
{
    public function events()
    {
        return $this->hasMany(Event::class, 'series_id');
    }
}
