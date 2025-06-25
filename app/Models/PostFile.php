<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Class PostFile
 *
 * @property int $id
 * @property int $post_id
 * @property int $file_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @mixin Builder
 */
class PostFile extends Model
{
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function file()
    {
        return $this->belongsTo(File::class);
    }
}
