<?php

namespace App\Models;

use Dyrynda\Database\Support\Casts\EfficientUuid;
use Dyrynda\Database\Support\GeneratesUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Class PostFile
 *
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property string $saved_name
 * @property int $file_size
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @mixin Builder
 */
class File extends Model
{
    use GeneratesUuid;

    protected $casts = [
        'uuid' => EfficientUuid::class,
    ];

    protected $fillable = [
        'name',
        'saved_name',
        'file_size',
    ];

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function posts()
    {
        return $this->hasManyThrough(Post::class, PostFile::class);
    }

    public function comments()
    {
        return $this->hasManyThrough(Comment::class, CommentFile::class);
    }
}
