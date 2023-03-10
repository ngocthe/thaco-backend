<?php

namespace Tests\Feature\Routes;

use App\Models\Admin;
use Database\Seeders\AdminSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Tests\Traits\InteractsWithUsers;
use Tests\Traits\InteractWithDomain;

class ApiAdminTest extends TestCase
{
    use InteractWithDomain, InteractsWithUsers, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Artisan::call('passport:install', ['-vvv' => true]);
        Artisan::call('passport:client', ['--password' => true, '--provider' => 'admins', '--name' => 'admins']);
        $this->seed(PermissionSeeder::class);
        $this->seed(AdminSeeder::class);

        $this->setUpDomain(env('BACKEND_LOCAL_DOMAIN'));
    }

    public function test_health_check_route()
    {
        $response = $this->get($this->getUrl('/'));

        $response
            ->assertStatus(200)
            ->assertExactJson([
                'status' => 'OK'
            ]);
    }

    public function test_admin_register_route()
    {
        $user = Admin::factory()->preRegister()->make();
        $userData = $user->getAttributes();

        $response = $this->post($this->getUrl('/register'), $userData);

        $response
            ->assertStatus(200)
            ->assertJson([
                'item' => Arr::except($user->toArray(), 'email_verified_at')
            ]);
    }

    public function test_admin_register_route_duplicated()
    {
        $userData = Admin::factory()->preRegister()->create()->getAttributes();

        $response = $this->post($this->getUrl('/register'), $userData);

        $response
            ->assertStatus(422)
            ->assertJson([
                'code' => 'InvalidParametersException',
                'message' => 'The given data was invalid.'
            ])
            ->assertJsonStructure(['code', 'message', 'errors']);
    }

    public function test_admin_login_route()
    {
        $password = '123456';
        $user = Admin::factory()->withPassword($password)->withDeleted()->create();

        $response = $this->post($this->getUrl('/login'), [
            'username' => $user->username,
            'password' => $password
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'token_type' => 'Bearer',
                'expires_in' => 31536000
            ])
            ->assertJsonStructure(['token_type', 'expires_in', 'access_token', 'refresh_token']);
    }

    public function test_admin_login_route_wrong_info()
    {
        $password = '123456';
        $user = Admin::factory()->withPassword($password)->withDeleted()->create();

        $response = $this->post($this->getUrl('/login'), [
            'username' => $user->username,
            'password' => $password . 7
        ]);

        $response
            ->assertStatus(401)
            ->assertJson([
                'code' => 'UnauthorizedException',
                'message' => 'Invalid username or password'
            ])
            ->assertJsonStructure(['code', 'message', 'errors']);
    }

    public function test_admin_logout_route()
    {
        $this->setUpUser(Admin::class);
        $this->login();
        $response = $this->post($this->getUrl('/logout'));

        $response
            ->assertStatus(200)
            ->assertJson([
                'message' => 'OK'
            ]);
    }

    public function test_admin_logout_route_unauthenticated()
    {
        $response = $this->post($this->getUrl('/logout'));

        $response
            ->assertStatus(422)
            ->assertJson([
                'code' => 'UnauthenticatedException',
                'message' => 'You are not authorized to perform this action!'
            ])
            ->assertJsonStructure(['code', 'message', 'errors']);
    }

    public function test_admin_refresh_token_route()
    {
        $password = '123456';
        $this->setUpUser(Admin::class, ['password' => Hash::make($password)]);

        $loginResponse = $this->post($this->getUrl('/login'), [
            'username' => $this->user->username,
            'password' => $password
        ]);
        $refreshToken = $loginResponse->getOriginalContent()['refresh_token'];

        $refreshResponse = $this->post($this->getUrl('/refresh_token'), [
            'refresh_token' => $refreshToken,
        ]);

        $refreshResponse
            ->assertStatus(200)
            ->assertJson([
                'token_type' => 'Bearer',
                'expires_in' => 31536000
            ])
            ->assertJsonStructure(['token_type', 'expires_in', 'access_token', 'refresh_token']);
    }

    public function test_admin_profile_route()
    {
        $this->setUpUser(Admin::class);

        $response = $this->get($this->getUrl('/profile'));

        $response
            ->assertStatus(200)
            ->assertJson([
                'user' => $this->user->toArray()
            ]);
    }

    public function test_admin_profile_route_unauthenticated()
    {
        $response = $this->get($this->getUrl('/profile'));

        $response
            ->assertStatus(422)
            ->assertJson([
                'code' => 'UnauthenticatedException',
                'message' => 'You are not authorized to perform this action!'
            ])
            ->assertJsonStructure(['code', 'message', 'errors']);
    }
}
