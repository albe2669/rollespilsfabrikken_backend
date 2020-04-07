<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\Forum;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CommentPolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability)
    {
        if ($user->isSuperUser()) {
            return true;
        }
    }

    /**
     * Determine whether the user can view any comments.
     *
     * @param User $user
     * @param Forum $forum
     * @return mixed
     */
    public function viewAny(User $user, Forum $forum)
    {
        return (new PolicyHelper())->checkLevel($user,  $forum['obj_id'], 2);
    }

    /**
     * Determine whether the user can view the comment.
     *
     * @param User $user
     * @param Comment $comment
     * @return mixed
     */
    public function view(User $user, Comment $comment)
    {
        return (new PolicyHelper())->checkLevel($user,  $comment->forum()['obj_id'], 2);
    }

    /**
     * Determine whether the user can create comments.
     *
     * @param User $user
     * @param Forum $forum
     * @return mixed
     */
    public function create(User $user, Forum $forum)
    {
        return (new PolicyHelper())->checkLevel($user,  $forum['obj_id'], 3);
    }

    /**
     * Determine whether the user can pin the comment.
     *
     * @param User $user
     * @param Comment $comment
     * @return mixed
     */
    public function pin(User $user, Comment $comment)
    {
        if ($comment['user_id'] == $user['id']) return true;

        return (new PolicyHelper())->checkLevel($user,  $comment->forum['obj_id'], 5);
    }

    /**
     * Determine whether the user can update the comment.
     *
     * @param User $user
     * @param Comment $comment
     * @return mixed
     */
    public function update(User $user, Comment $comment)
    {
        if ($comment['user_id'] == $user['id']) return true;

        return (new PolicyHelper())->checkLevel($user,  $comment->forum['obj_id'], 5);
    }

    /**
     * Determine whether the user can delete the comment.
     *
     * @param User $user
     * @param Comment $comment
     * @return mixed
     */
    public function delete(User $user, Comment $comment)
    {
        if ($comment['user_id'] == $user['id']) return true;

        return (new PolicyHelper())->checkLevel($user,  $comment->forum['obj_id'], 5);
    }

    /**
     * Determine whether the user can restore the comment.
     *
     * @param User $user
     * @param Comment $comment
     * @return mixed
     */
    public function restore(User $user, Comment $comment)
    {
        return (new PolicyHelper())->checkLevel($user,  $comment->forum['obj_id'], 5);
    }

    /**
     * Determine whether the user can permanently delete the comment.
     *
     * @param User $user
     * @param Comment $comment
     * @return mixed
     */
    public function forceDelete(User $user, Comment $comment)
    {
        return (new PolicyHelper())->checkLevel($user,  $comment->forum()['obj_id'], 5);
    }
}
