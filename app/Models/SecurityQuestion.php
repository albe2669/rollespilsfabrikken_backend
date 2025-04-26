<?php

namespace App\Models;

use Dyrynda\Database\Support\Casts\EfficientUuid;;
use Dyrynda\Database\Support\GeneratesUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Class SecurityQuestion
 *
 * @property int $id
 * @property string $uuid
 * @property string $question
 * @property string $answer
 * @property Carbon $last_answered_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @mixin Builder
 */
class SecurityQuestion extends Model
{
    use GeneratesUuid;

    protected $casts = [
        'uuid' => EfficientUuid::class,
    ];

    protected $fillable = [
        'question',
        'answer',
    ];

    public function getRouteKeyName()
    {
        return 'uuid';
    }
}
