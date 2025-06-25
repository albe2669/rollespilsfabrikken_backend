<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Class PasswordReset
 *
 * @property int $id
 * @property string $email
 * @property string $token
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @mixin Builder
 */
class PasswordReset extends Model
{
    protected $fillable = [
        'email', 'token',
    ];
}
