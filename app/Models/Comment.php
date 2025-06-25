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

class Comment extends Model
{
    use GeneratesUuid;
    use HasFactory;
    use Searchable;

    protected $casts = [
        'uuid' => EfficientUuid::class,
        'pinned' => 'boolean',
    ];

    protected $fillable = [
        'body',
        'user_id',
    ];

    public function toSearchableArray()
    {
        $array = $this->toArray();

        $array = Arr::only($array, [
            'id',
            'title',
            'body',
        ]);

        return $array;
    }

    public function files()
    {
        return $this->hasManyThrough(File::class, CommentFile::class, 'comment_id', 'id', 'id', 'file_id');
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function forum()
    {
        return $this->post->forum();
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    public function childComments()
    {
        return $this->hasMany(Comment::class, 'parent_id')->with('childComments');
    }

    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id', 'id');
    }

    public function getTableColumns()
    {
        return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
    }
}
