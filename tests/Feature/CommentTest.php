<?php

use App\Models\Forum;
use App\Models\Post;
use App\Models\User;
use Tests\Helpers\TestHelper;

test('create comment with middleware returns 401', function () {
    $data = [
        'body' => '# Comment body\n## This\n### Is\nFor\n- Unit\n- Testing',
    ];

    $forum = Forum::factory()->create();
    $post = Post::factory()->create(['forum_id' => $forum['id']]);

    $this
        ->json('POST', '/api/forum/'.$forum['uuid'].'/post/'.$post['uuid'].'/comment', $data)
        ->assertStatus(401)
        ->assertJson(['message' => 'Unauthenticated.']);
});

test('create comment without roles returns 403', function () {
    $data = [
        'body' => '# Comment body\n## This\n### Is\nFor\n- Unit\n- Testing',
    ];

    $forum = Forum::factory()->create();
    $post = Post::factory()->create(['forum_id' => $forum['id']]);
    $user = User::factory()->create();

    $this
        ->actingAs($user, 'sanctum')
        ->json('POST', '/api/forum/'.$forum['uuid'].'/post/'.$post['uuid'].'/comment', $data)
        ->assertStatus(403)
        ->assertJson(['message' => 'You do not have the rights to perform this action']);
});

test('create comment with roles succeeds', function () {
    $data = [
        'body' => '# Comment body\n## This\n### Is\nFor\n- Unit\n- Testing',
    ];

    $forum = Forum::factory()->create();
    $post = Post::factory()->create(['forum_id' => $forum['id']]);
    $user = User::factory()->create();

    (new TestHelper)->giveUserPermission($user, $forum['obj_id'], 3);

    $this
        ->actingAs($user, 'sanctum')
        ->json('POST', '/api/forum/'.$forum['uuid'].'/post/'.$post['uuid'].'/comment', $data)
        ->assertStatus(201)
        ->assertJson([
            'message' => 'success',
            'comment' => [
                'body' => $data['body'],
                'user_id' => $user['uuid'],
            ],
        ]);
});

test('create sub comment with roles succeeds', function () {
    $forum = Forum::factory()->create();
    $post = Post::factory()->create(['forum_id' => $forum['id']]);
    $parent_comment = $post->comments()->crea

    $comment = $post->comments()->first();

    $data = [
        'body' => '# Comment body\n## This\n### Is\nFor\n- Unit\n- Testing',
        'parent_id' => $comment->uuid;
    ];

    $user = User::factory()->create();

    (new TestHelper)->giveUserPermission($user, $forum['obj_id'], 3);

    $this
        ->actingAs($user, 'sanctum')
        ->json('POST', '/api/forum/'.$forum['uuid'].'/post/'.$post['uuid'].'/comment', $data)
        ->assertStatus(201)
        ->assertJson([
            'message' => 'success',
            'comment' => [
                'body' => $data['body'],
                'parent_id' => $data['parent_id'],
                'user_id' => $user['uuid'],
            ],
        ]);
});

test('create comment as admin succeeds', function () {
    $data = [
        'body' => '# Comment body\n## This\n### Is\nFor\n- Unit\n- Testing',
    ];

    $forum = Forum::factory()->create();
    $post = Post::factory()->create(['forum_id' => $forum['id']]);
    $user = User::factory()->create();

    $user['super_user'] = 1;
    $user->save();

    $this
        ->actingAs($user, 'sanctum')
        ->json('POST', '/api/forum/'.$forum['uuid'].'/post/'.$post['uuid'].'/comment', $data)
        ->assertStatus(201)
        ->assertJson([
            'message' => 'success',
            'comment' => [
                'body' => $data['body'],
                'user_id' => $user['uuid'],
            ],
        ]);
});

test('get all comments succeeds', function () {
    $forum = Forum::factory()->create();
    $post = Post::factory()->create(['forum_id' => $forum['id']]);
    $user = User::factory()->create();

    (new TestHelper)->giveUserPermission($user, $forum['obj_id'], 2);

    $this
        ->actingAs($user, 'sanctum')
        ->json('GET', '/api/forum/'.$forum['uuid'].'/post/'.$post['uuid'].'/comment')
        ->assertStatus(200)
        ->assertJson([
            'message' => 'success',
        ]);
});

test('update comment as owner succeeds', function () {
    $data = [
        'body' => '# Updated comment body\n## This\n### Is\nFor\n- Unit\n- Testing',
    ];

    $forum = Forum::factory()->create();
    $user = User::factory()->create();
    $post = Post::factory()->create(['forum_id' => $forum['id']]);

    $comment = $post->comments()->first();
    $comment->user_id = $user['id'];
    $comment->save();

    (new TestHelper)->giveUserPermission($user, $forum['obj_id'], 2);

    $post = $this
        ->actingAs($user, 'sanctum')
        ->json('GET', '/api/forum/'.$forum['uuid'].'/post')
        ->assertStatus(200)
        ->decodeResponseJson()['data']['posts'][1];

    $this
        ->actingAs($user, 'sanctum')
        ->json('PATCH', '/api/forum/'.$forum['uuid'].'/post/'.$post['id'].'/comment/'.$comment['uuid'], $data)
        ->assertStatus(200)
        ->assertJson([
            'message' => 'success',
            'comment' => [
                'body' => $data['body'],
                'user_id' => $user['uuid'],
            ],
        ]);
});

test('delete comment as owner succeeds', function () {
    $forum = Forum::factory()->create();
    $user = User::factory()->create();
    $post = Post::factory()->create(['forum_id' => $forum['id']]);
    $comment = $post->comments()->first();
    $comment->user_id = $user['id'];
    $comment->save();

    $this
        ->actingAs($user, 'sanctum')
        ->json('DELETE', '/api/forum/'.$forum['uuid'].'/post/'.$post['uuid'].'/comment/'.$comment['uuid'])
        ->assertStatus(200)
        ->assertJson([
            'message' => 'success',
        ]);
});

test('delete comment as moderator succeeds', function () {
    $forum = Forum::factory()->create();
    $user = User::factory()->create();
    $post = Post::factory()->create(['forum_id' => $forum['id']]);
    $comment = $post->comments()->first();

    (new TestHelper)->giveUserPermission($user, $forum['obj_id'], 5);

    $this
        ->actingAs($user, 'sanctum')
        ->json('DELETE', '/api/forum/'.$forum['uuid'].'/post/'.$post['uuid'].'/comment/'.$comment['uuid'])
        ->assertStatus(200)
        ->assertJson([
            'message' => 'success',
        ]);
});
