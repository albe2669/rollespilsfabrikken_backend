<?php

use App\Models\Calendar;
use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;
use Tests\Helpers\TestHelper;

test('create event with middleware returns 401', function () {
    $data = [
        'title' => 'Event title for unit',
        'description' => '# Event description\n## This\n### Is\nFor\n- Unit\n- Testing',
        'start' => '01-01-2022 23:02:01',
        'end' => '01-01-2022 23:30:01',
    ];

    $calendar = Calendar::factory()->create();

    $this
        ->json('POST', '/api/calendar/'.$calendar['uuid'].'/event', $data)
        ->assertStatus(401)
        ->assertJson(['message' => 'Unauthenticated.']);
});

test('create event without roles returns 403', function () {
    $data = [
        'title' => 'Event title for unit',
        'description' => '# Event description\n## This\n### Is\nFor\n- Unit\n- Testing',
        'start' => '01-01-2022 23:02:01',
        'end' => '01-01-2022 23:30:01',
    ];

    $calendar = Calendar::factory()->create();
    $user = User::factory()->create();

    $this
        ->actingAs($user, 'sanctum')
        ->json('POST', '/api/calendar/'.$calendar['uuid'].'/event', $data)
        ->assertStatus(403)
        ->assertJson(['message' => 'You do not have the rights to perform this action']);
});

test('create event with roles succeeds', function () {
    $data = [
        'title' => 'Event title for unit',
        'description' => '# Event description\n## This\n### Is\nFor\n- Unit\n- Testing',
        'start' => '01-01-2022 23:02:01',
        'end' => '01-01-2022 23:30:01',
    ];

    $calendar = Calendar::factory()->create();
    $user = User::factory()->create();

    (new TestHelper)->giveUserPermission($user, $calendar['obj_id'], 4);

    $this
        ->actingAs($user, 'sanctum')
        ->json('POST', '/api/calendar/'.$calendar['uuid'].'/event', $data)
        ->assertStatus(201)
        ->assertJson([
            'message' => 'success',
            'event' => [
                'title' => $data['title'],
                'description' => $data['description'],
                'user_id' => $user['uuid'],
            ],
        ])
        ->assertJsonStructure(
            [
                'message',
                'event' => [
                    'id',
                    'title',
                    'description',
                    'user_id',
                    'created_at',
                    'updated_at',
                    'permissions' => [
                        'can_update',
                        'can_delete',
                    ],
                ],
            ],
        );
});

test('create event as admin succeeds', function () {
    $data = [
        'title' => 'Event title for unit',
        'description' => '# Event description\n## This\n### Is\nFor\n- Unit\n- Testing',
        'start' => '01-01-2022 23:02:01',
        'end' => '01-01-2022 23:30:01',
    ];

    $calendar = Calendar::factory()->create();
    $user = User::factory()->create();

    $user['super_user'] = 1;
    $user->save();

    $this
        ->actingAs($user, 'sanctum')
        ->json('POST', '/api/calendar/'.$calendar['uuid'].'/event', $data)
        ->assertStatus(201)
        ->assertJson([
            'message' => 'success',
            'event' => [
                'title' => $data['title'],
                'description' => $data['description'],
                'user_id' => $user['uuid'],
            ],
        ])
        ->assertJsonStructure(
            [
                'message',
                'event' => [
                    'title',
                    'description',
                    'user_id',
                    'created_at',
                    'updated_at',
                    'id',
                    'permissions' => [
                        'can_update',
                        'can_delete',
                    ],
                ],
            ],
        );
});

test('get all events succeeds', function () {
    $calendar = Calendar::factory()->create();
    $user = User::factory()->create();

    (new TestHelper)->giveUserPermission($user, $calendar['obj_id'], 2);

    $this
        ->actingAs($user, 'sanctum')
        ->json('GET', '/api/calendar/'.$calendar['uuid'].'/event')
        ->assertStatus(200)
        ->assertJson([
            'message' => 'success',
        ])
        ->assertJsonStructure(
            [
                'message',
                'data' => [
                    'events' => [
                        [
                            'id',
                            'user' => [
                                'id',
                                'username',
                                'avatar_url',
                                'created_at',
                            ],
                            'title',
                            'description',
                            'created_at',
                            'updated_at',
                            'permissions' => [
                                'can_update',
                                'can_delete',
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

test('update event as owner succeeds', function () {
    $data = [
        'title' => 'Updated event title for unit',
        'description' => '# Update Event description\n## This\n### Is\nFor\n- Unit\n- Testing',
        'start' => '01-01-2022 23:02:01',
        'end' => '01-01-2022 23:03:01',
    ];

    $calendar = Calendar::factory()->create();
    $user = User::factory()->create();

    $event = (new Event)
        ->fill([
            'title' => 'hello',
            'description' => 'hello again',
            'start' => Carbon::createFromFormat('d-m-Y H:i:s', '01-01-2022 23:02:01')->toDateTimeString(),
            'end' => Carbon::createFromFormat('d-m-Y H:i:s', '01-01-2022 23:03:01')->toDateTimeString(),
        ])
        ->user()
        ->associate($user);
    $calendar->events()->save($event);

    (new TestHelper)->giveUserPermission($user, $calendar['obj_id'], 2);

    $event = $this
        ->actingAs($user, 'sanctum')
        ->json('GET', '/api/calendar/'.$calendar['uuid'].'/event')
        ->assertStatus(200)
        ->decodeResponseJson()['data']['events'][1];

    $this
        ->actingAs($user, 'sanctum')
        ->json('PATCH', '/api/calendar/'.$calendar['uuid'].'/event/'.$event['id'], $data)
        ->assertStatus(200)
        ->assertJson([
            'message' => 'success',
            'event' => [
                'title' => $data['title'],
                'description' => $data['description'],
                'user_id' => $user['uuid'],
            ],
        ])
        ->assertJsonStructure(
            [
                'message',
                'event' => [
                    'title',
                    'description',
                    'user_id',
                    'created_at',
                    'updated_at',
                    'id',
                    'permissions' => [
                        'can_update',
                        'can_delete',
                    ],
                ],
            ],
        );
});

test('delete event as owner succeeds', function () {
    $calendar = Calendar::factory()->create();
    $user = User::factory()->create();

    $event = (new Event)
        ->fill([
            'title' => 'hello',
            'description' => 'hello again',
            'start' => Carbon::createFromFormat('d-m-Y H:i:s', '01-01-2022 23:02:01')->toDateTimeString(),
            'end' => Carbon::createFromFormat('d-m-Y H:i:s', '01-01-2022 23:03:01')->toDateTimeString(),
        ])
        ->user()
        ->associate($user);
    $calendar->events()->save($event);

    (new TestHelper)->giveUserPermission($user, $calendar['obj_id'], 2);

    $event = $this
        ->actingAs($user, 'sanctum')
        ->json('GET', '/api/calendar/'.$calendar['uuid'].'/event')
        ->assertStatus(200)
        ->decodeResponseJson()['data']['events'][1];

    $this
        ->actingAs($user, 'sanctum')
        ->json('DELETE', '/api/calendar/'.$calendar['uuid'].'/event/'.$event['id'])
        ->assertStatus(200)
        ->assertJson([
            'message' => 'success',
        ]);
});

test('delete event as moderator succeeds', function () {
    $calendar = Calendar::factory()->create();
    $user = User::factory()->create();

    $event = (new Event)
        ->fill([
            'title' => 'hello',
            'description' => 'hello again',
            'start' => Carbon::createFromFormat('d-m-Y H:i:s', '01-01-2022 23:02:01')->toDateTimeString(),
            'end' => Carbon::createFromFormat('d-m-Y H:i:s', '01-01-2022 23:03:01')->toDateTimeString(),
        ])
        ->user()
        ->associate($user);
    $calendar->events()->save($event);

    (new TestHelper)->giveUserPermission($user, $calendar['obj_id'], 5);

    $event = $this
        ->actingAs($user, 'sanctum')
        ->json('GET', '/api/calendar/'.$calendar['uuid'].'/event')
        ->assertStatus(200)
        ->decodeResponseJson()['data']['events'][1];

    $this
        ->actingAs($user, 'sanctum')
        ->json('DELETE', '/api/calendar/'.$calendar['uuid'].'/event/'.$event['id'])
        ->assertStatus(200)
        ->assertJson([
            'message' => 'success',
        ]);
});