<?php

use App\Models\Calendar;
use App\Models\User;
use Tests\Helpers\TestHelper;

test('create calendar with middleware returns 401', function () {
    $data = [
        'title' => 'Calendar title for unit',
        'description' => 'Calendar description for unit',
        'colour' => '#9448bc',
    ];

    $this
        ->json('POST', '/api/calendar/', $data)
        ->assertStatus(401)
        ->assertJson(['message' => 'Unauthenticated.']);
});

test('create calendar without roles returns 403', function () {
    $data = [
        'title' => 'Calendar title for unit',
        'description' => 'Calendar description for unit',
        'colour' => '#9448bc',
    ];

    $user = User::factory()->create();

    $this
        ->actingAs($user, 'sanctum')
        ->json('POST', '/api/calendar/', $data)
        ->assertStatus(403)
        ->assertJson(['message' => 'You do not have the rights to perform this action']);
});

test('create calendar with roles returns 403', function () {
    $data = [
        'title' => 'Calendar title for unit',
        'description' => 'Calendar description for unit',
        'colour' => '#9448bc',
    ];

    $user = User::factory()->create();

    $this
        ->actingAs($user, 'sanctum')
        ->json('POST', '/api/calendar/', $data)
        ->assertStatus(403)
        ->assertJson(['message' => 'You do not have the rights to perform this action']);
});

test('create calendar as admin succeeds', function () {
    $data = [
        'title' => 'Calendar title for unit',
        'description' => 'Calendar description for unit',
        'colour' => '#9448bc',
    ];

    $user = User::factory()->create();

    $user['super_user'] = 1;
    $user->save();

    $this
        ->actingAs($user, 'sanctum')
        ->json('POST', '/api/calendar/', $data)
        ->assertStatus(201)
        ->assertJson([
            'message' => 'success',
            'calendar' => [
                'name' => $data['title'],
                'description' => $data['description'],
                'colour' => $data['colour'],
            ],
        ])
        ->assertJsonStructure(
            [
                'message',
                'calendar' => [
                    'id',
                    'name',
                    'description',
                    'colour',
                    'permissions' => [
                        'can_update',
                        'can_delete',
                        'can_add_events',
                    ],
                ],
            ],
        );
});

test('get all calendars as admin succeeds', function () {
    $calendar = Calendar::factory()->create();
    $user = User::factory()->create();

    $user['super_user'] = 1;
    $user->save();

    $this
        ->actingAs($user, 'sanctum')
        ->json('GET', '/api/calendar')
        ->assertStatus(200)
        ->assertJson([
            'message' => 'success',
        ])
        ->assertJsonStructure(
            [
                'message',
                'data' => [
                    'calendars' => [
                        [
                            'id',
                            'name',
                            'description',
                            'colour',
                            'permissions' => [
                                'can_update',
                                'can_delete',
                                'can_add_events',
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

test('get all calendars as user succeeds', function () {
    $calendar = Calendar::factory()->create();
    $user = User::factory()->create();

    (new TestHelper)->giveUserPermission($user, $calendar['obj_id'], 2);

    $this
        ->actingAs($user, 'sanctum')
        ->json('GET', '/api/calendar')
        ->assertStatus(200)
        ->assertJson([
            'message' => 'success',
        ])
        ->assertJsonStructure(
            [
                'message',
                'data' => [
                    'calendars' => [
                        [
                            'id',
                            'name',
                            'description',
                            'colour',
                            'permissions' => [
                                'can_update',
                                'can_delete',
                                'can_add_events',
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

test('update calendar as user succeeds', function () {
    $data = [
        'title' => 'Updated calendar title for unit',
        'description' => 'Updated calendar description for unit',
        'colour' => '#9448bc',
    ];

    $calendar = Calendar::factory()->create();
    $user = User::factory()->create();

    (new TestHelper)->giveUserPermission($user, $calendar['obj_id'], 6);

    $this
        ->actingAs($user, 'sanctum')
        ->json('PATCH', '/api/calendar/'.$calendar['uuid'], $data)
        ->assertStatus(200)
        ->assertJson([
            'message' => 'success',
            'calendar' => [
                'name' => $data['title'],
                'description' => $data['description'],
                'id' => $calendar['uuid'],
                'colour' => $data['colour'],
            ],
        ])
        ->assertJsonStructure(
            [
                'message',
                'calendar' => [
                    'id',
                    'name',
                    'description',
                    'colour',
                    'permissions' => [
                        'can_update',
                        'can_delete',
                        'can_add_events',
                    ],
                ],
            ],
        );
});

test('update calendar as admin succeeds', function () {
    $data = [
        'title' => 'Updated calendar title for unit',
        'description' => 'Updated calendar description for unit',
        'colour' => '#9448bc',
    ];

    $calendar = Calendar::factory()->create();
    $user = User::factory()->create();

    $user['super_user'] = 1;
    $user->save();

    $this
        ->actingAs($user, 'sanctum')
        ->json('PATCH', '/api/calendar/'.$calendar['uuid'], $data)
        ->assertStatus(200)
        ->assertJson([
            'message' => 'success',
            'calendar' => [
                'name' => $data['title'],
                'description' => $data['description'],
                'id' => $calendar['uuid'],
                'colour' => $data['colour'],
            ],
        ])
        ->assertJsonStructure(
            [
                'message',
                'calendar' => [
                    'id',
                    'name',
                    'description',
                    'colour',
                    'permissions' => [
                        'can_update',
                        'can_delete',
                        'can_add_events',
                    ],
                ],
            ],
        );
});

test('delete calendar succeeds', function () {
    $user = User::factory()->create();

    $user['super_user'] = 1;
    $user->save();

    $calendar = $this
        ->actingAs($user, 'sanctum')->json('GET', '/api/calendar')
        ->assertStatus(200)
        ->decodeResponseJson()['data']['calendars'][1];

    $this
        ->actingAs($user, 'sanctum')->json('DELETE', '/api/calendar/'.$calendar['id'])
        ->assertStatus(200)
        ->assertJson([
            'message' => 'success',
        ]);
});