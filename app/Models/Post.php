<?php

namespace App\Models;

use Carbon\Carbon;
use Dyrynda\Database\Support\Casts\EfficientUuid;;
use Dyrynda\Database\Support\GeneratesUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Laravel\Scout\Searchable;

/**
 * Class Post
 *
 * @property int $id
 * @property string $uuid
 * @property int $forum_id
 * @property int $user_id
 * @property string $title
 * @property string $body
 * @property bool $pinned
 * @property bool $locked
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * @mixin Builder
 */
class Post extends Model
{
    use Searchable, GeneratesUuid, HasFactory;

    protected static function boot()
    {
        parent::boot();

        static::retrieved(function($model){
            if (!auth()->user()) {
                return;
            }

            $model->relevance = $model->getRelevance();
        });
    }

    // Model specific variables
    public int $relevance;
    private static array $relevanceLookup = [
        'new_comment' => 5,
        'new_post' => 4,
        'new_comment_on_users_comment_post' => 3,
        'new_comment_on_users_comment' => 2,
        'new_comment_on_users_post' => 1
    ];

    protected $casts = [
        'uuid' => EfficientUuid::class,
        'pinned' => 'boolean'
    ];

    protected $fillable = [
        'title',
        'body'
    ];

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function toSearchableArray() {
        $array = $this->toArray();

        $array = Arr::only($array, [
            'id',
            'title',
            'body'
        ]);

        return $array;
    }

    public function forum() {
        return $this->belongsTo('App\Models\Forum');
    }

    public function user() {
        return $this->belongsTo('App\Models\User');
    }

    public function comments() {
        return $this->hasMany('App\Models\Comment');
    }

    public function files() {
        return $this->hasManyThrough('App\Models\File', 'App\Models\PostFile', 'post_id', 'id', 'id', 'file_id');
    }

    public function getThreadedComments() {
        return $this->comments()->with('user')->get()->threaded();
    }

    public function getTableColumns() {
        return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
    }

    private function isUsersPost() {
        return auth()->user()['id'] === $this->id;
    }

    private function getLatestCommentQuery() {
        return (new Comment)
            ->where('post_id', '=', $this->id)
            ->orderBy('created_at', 'desc')
            ->select('id', 'created_at');
    }

    private function getLatestComment() {
        return $this->getLatestCommentQuery()
            ->where('user_id', '!=', auth()->user()->id)
            ->first();
    }

    private function getDaysSince(string $date) : int {
        return Carbon::createFromFormat('Y-m-d H:i:s', $date)->diffInDays(Carbon::now());
    }
    private function calculateRelevance(int $relevance, int $daysPassed) : int {
        return ($relevance * $daysPassed) + $relevance;
    }

    public function getRelevance() : int {
        $relevance = array();

        // TODO: Determine if this should only be root comments
        // Get the latest comment on the post
        $comment = $this->getLatestComment(); // TODO: Check if any code paths doesnt use this

        // TODO: Optimize this shit

        // If the post is created by the current user
        if ($this->isUsersPost() && $comment != null) {
            // No need to calculate any other relevance stats
            return $this->calculateRelevance(
                $this->getDaysSince($comment->created_at),
                self::$relevanceLookup['new_comment_on_users_post']
            );
        }

        // Calculate relevance for a new post
        $relevance[] = $this->calculateRelevance(
            $this->getDaysSince($this->created_at),
            self::$relevanceLookup['new_post']
        );

        // If the post is not created by the current user
        if ($comment != null) {
            // Get latest comment by the current user
            $userComment = $this->getLatestCommentQuery()
                ->where('user_id', '=', auth()->user()->id)
                ->first();

            if ($userComment == null) {
                // User hasn't commented on this post
                $relevance[] = $this->calculateRelevance(
                    $this->getDaysSince($comment->created_at),
                    self::$relevanceLookup['new_comment']
                );
            } else {
                // User has commented on this post
                // Calculate relevance for comment on post user has commented on
                $relevance[] = $this->calculateRelevance(
                    $this->getDaysSince($comment->created_at),
                    self::$relevanceLookup['new_comment_on_users_comment_post']
                );

                // Get the newest sub comment to users comment
                $subComment = $this->getLatestCommentQuery()
                    ->where('user_id', '!=', auth()->user()->id)
                    ->where('parent_id', '=', $userComment->id)
                    ->first();

                // If such a comment exists, calculate the relevance for it
                if ($subComment !== null) {
                    $relevance[] = $this->calculateRelevance(
                        $this->getDaysSince($subComment->created_at),
                        self::$relevanceLookup['new_comment_on_users_comment']
                    );
                }
            }
        }

        // Return the highest relevance point
        return max($relevance);
    }
}
