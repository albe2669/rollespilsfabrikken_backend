<?php

namespace Database\Seeders\Production;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravolt\Avatar\Avatar;

class UserProductionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create the admin
        $user = (new User)->create([
            'username' => 'Admin',
            'email' => 'admin@rollespilsfabrikken.dk',
            'password' => Hash::make('Y`oMJLE)\fNR=Mf-|43j+H%qq`<~'),
            'activation_token' => Str::random(60)
        ]);

        $user->refresh();
        $user->active = 1;
        $user->super_user = 1;
        $user->email_verified_at = Carbon\Carbon::now();

        $user->save();

        $avatar = (new Avatar)
            ->create($user->username)
            ->getImageObject()
            ->encode('png');

        Storage::disk('local')->put('public/avatars/' . $user->uuid . '/avatar.png', (string) $avatar);
    }
}
