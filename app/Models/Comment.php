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
 * Class Comment
 *
 * @property int $id
 * @property string $uuid
 * @property int $post_id
 * @property int $parent_id
 * @property int $user_id
 * @property string $body
 * @property bool $pinned
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @mixin Builder
 */
class Comment extends Model
{
    use Searchable, GeneratesUuid, HasFactory;

    protected $casts = [
        'uuid' => EfficientUuid::class,
        'pinned' => 'boolean'
    ];

    protected $fillable = [
        'body',
        'user_id'
    ];

    public function toSearchableArray() {
        $array = $this->toArray();

        $array = Arr::only($array, [
            'id',
            'title',
            'body'
        ]);

        return $array;
    }

    public function files() {
        return $this->hasManyThrough('App\Models\File', 'App\Models\CommentFile', 'comment_id', 'id', 'id', 'file_id');
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function user() {
        return $this->belongsTo('App\Models\User');
    }

    public function post() {
        return $this->belongsTo('App\Models\Post');
    }

    public function forum() {
        return $this->post->forum();
    }

    public function comments() {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    public function childComments() {
        return $this->hasMany(Comment::class, 'parent_id')->with('childComments');
    }

    public function parent() {
        return $this->belongsTo('App\Models\Comment', 'parent_id', 'id');
    }

    public function getTableColumns() {
        return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
    }
}
