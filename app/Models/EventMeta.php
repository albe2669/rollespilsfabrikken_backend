<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Class EventMeta
 *
 * @property int $id
 * @property string $event_id
 * @property int $repeat_start
 * @property int $repeat_interval
 * @property int $repeat_end
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @mixin Builder
 */
class EventMeta extends Model
{
    protected $fillable = [
        'repeat_start',
        'repeat_interval',
        'repeat_end',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
