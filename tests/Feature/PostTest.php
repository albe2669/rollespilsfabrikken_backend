<?php

use App\Models\Forum;
use App\Models\Post;
use App\Models\User;
use Tests\Helpers\TestHelper;

test('create post with middleware returns 401', function () {
    $data = [
        'title' => 'Post title for unit',
        'body' => '# Post body\n## This\n### Is\nFor\n- Unit\n- Testing',
    ];

    $forum = Forum::factory()->create();

    $this
        ->json('POST', '/api/forum/'.$forum['uuid'].'/post', $data)
        ->assertStatus(401)
        ->assertJson(['message' => 'Unauthenticated.']);
});

test('create post without roles returns 403', function () {
    $data = [
        'title' => 'Post title for unit',
        'body' => '# Post body\n## This\n### Is\nFor\n- Unit\n- Testing',
    ];

    $forum = Forum::factory()->create();
    $user = User::factory()->create();

    $this
        ->actingAs($user, 'sanctum')
        ->json('POST', '/api/forum/'.$forum['uuid'].'/post', $data)
        ->assertStatus(403)
        ->assertJson(['message' => 'You do not have the rights to perform this action']);
});

test('create post with roles succeeds', function () {
    $data = [
        'title' => 'Post title for unit',
        'body' => '# Post body\n## This\n### Is\nFor\n- Unit\n- Testing',
    ];

    $forum = Forum::factory()->create();
    $user = User::factory()->create();

    (new TestHelper)->giveUserPermission($user, $forum['obj_id'], 4);

    $this
        ->actingAs($user, 'sanctum')
        ->json('POST', '/api/forum/'.$forum['uuid'].'/post', $data)
        ->assertStatus(201)
        ->assertJson([
            'message' => 'success',
            'post' => [
                'title' => $data['title'],
                'body' => $data['body'],
                'user_id' => $user['uuid'],
            ],
        ])
        ->assertJsonStructure(
            [
                'message',
                'post' => [
                    'id',
                    'user_id',
                    'title',
                    'body',
                    'created_at',
                    'updated_at',
                    'permissions' => [
                        'can_update',
                        'can_delete',
                        'can_add_comments',
                    ],
                ],
            ],
        );
});

test('create post as admin succeeds', function () {
    $data = [
        'title' => 'Post title for unit',
        'body' => '# Post body\n## This\n### Is\nFor\n- Unit\n- Testing',
    ];

    $forum = Forum::factory()->create();
    $user = User::factory()->create();

    $user['super_user'] = 1;
    $user->save();

    $this
        ->actingAs($user, 'sanctum')
        ->json('POST', '/api/forum/'.$forum['uuid'].'/post', $data)
        ->assertStatus(201)
        ->assertJson([
            'message' => 'success',
            'post' => [
                'title' => $data['title'],
                'body' => $data['body'],
                'user_id' => $user['uuid'],
            ],
        ])
        ->assertJsonStructure(
            [
                'message',
                'post' => [
                    'id',
                    'user_id',
                    'title',
                    'body',
                    'created_at',
                    'updated_at',
                    'permissions' => [
                        'can_update',
                        'can_delete',
                        'can_add_comments',
                    ],
                ],
            ],
        );
});

test('get all posts succeeds', function () {
    $forum = Forum::factory()->create();
    $user = User::factory()->create();

    (new TestHelper)->giveUserPermission($user, $forum['obj_id'], 2);

    $this
        ->actingAs($user, 'sanctum')
        ->json('GET', '/api/forum/'.$forum['uuid'].'/post')
        ->assertStatus(200)
        ->assertJson([
            'message' => 'success',
        ])
        ->assertJsonStructure(
            [
                'message',
                'data' => [
                    'posts' => [
                        [
                            'id',
                            'user' => [
                                'id',
                                'username',
                                'avatar_url',
                                'created_at',
                            ],
                            'title',
                            'created_at',
                            'updated_at',
                            'permissions' => [
                                'can_update',
                                'can_delete',
                                'can_add_comments',
                            ],
                        ],
                    ],
                    'links' => [
                        'first_page',
                        'last_page',
                        'prev_page',
                        'next_page',
                    ],
                    'meta' => [
                        'current_page',
                        'first_item',
                        'last_item',
                        'per_page',
                        'total',
                    ],
                ],
            ],
        );
});

test('update post as owner succeeds', function () {
    $data = [
        'title' => 'Updated post title for unit',
        'body' => '# Updated post body\n## This\n### Is\nFor\n- Unit\n- Testing',
    ];

    $forum = Forum::factory()->create();
    $user = User::factory()->create();

    $post = (new Post)
        ->fill(['title' => 'hello', 'body' => 'hello again'])
        ->user()
        ->associate($user);
    $forum->posts()->save($post);

    (new TestHelper)->giveUserPermission($user, $forum['obj_id'], 2);

    $post = $this
        ->actingAs($user, 'sanctum')
        ->json('GET', '/api/forum/'.$forum['uuid'].'/post')
        ->assertStatus(200)
        ->decodeResponseJson()['data']['posts'][1];

    $this
        ->actingAs($user, 'sanctum')
        ->json('PATCH', '/api/forum/'.$forum['uuid'].'/post/'.$post['id'], $data)
        ->assertStatus(200)
        ->assertJson([
            'message' => 'success',
            'post' => [
                'title' => $data['title'],
                'body' => $data['body'],
                'user_id' => $user['uuid'],
            ],
        ])
        ->assertJsonStructure(
            [
                'message',
                'post' => [
                    'id',
                    'user_id',
                    'title',
                    'body',
                    'created_at',
                    'updated_at',
                    'permissions' => [
                        'can_update',
                        'can_delete',
                        'can_add_comments',
                    ],
                ],
            ],
        );
});

test('delete post as owner succeeds', function () {
    $forum = Forum::factory()->create();
    $user = User::factory()->create();

    $post = (new Post)
        ->fill(['title' => 'hello', 'body' => 'hello again'])
        ->user()
        ->associate($user);
    $forum->posts()->save($post);

    (new TestHelper)->giveUserPermission($user, $forum['obj_id'], 2);

    $post = $this
        ->actingAs($user, 'sanctum')
        ->json('GET', '/api/forum/'.$forum['uuid'].'/post')
        ->assertStatus(200)
        ->decodeResponseJson()['data']['posts'][1];

    $this
        ->actingAs($user, 'sanctum')
        ->json('DELETE', '/api/forum/'.$forum['uuid'].'/post/'.$post['id'])
        ->assertStatus(200)
        ->assertJson([
            'message' => 'success',
        ]);
});

test('delete post as moderator succeeds', function () {
    $forum = Forum::factory()->create();
    $user = User::factory()->create();

    $post = (new Post)
        ->fill(['title' => 'hello', 'body' => 'hello again'])
        ->user()
        ->associate($user);
    $forum->posts()->save($post);

    (new TestHelper)->giveUserPermission($user, $forum['obj_id'], 5);

    $post = $this
        ->actingAs($user, 'sanctum')
        ->json('GET', '/api/forum/'.$forum['uuid'].'/post')
        ->assertStatus(200)
        ->decodeResponseJson()['data']['posts'][1];

    $this
        ->actingAs($user, 'sanctum')
        ->json('DELETE', '/api/forum/'.$forum['uuid'].'/post/'.$post['id'])
        ->assertStatus(200)
        ->assertJson([
            'message' => 'success',
        ]);
});
