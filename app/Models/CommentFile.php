<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class CommentFile extends Model
{
    public function comment()
    {
        return $this->belongsTo(Comment::class);
    }

    public function file()
    {
        return $this->belongsTo(File::class);
    }
}
