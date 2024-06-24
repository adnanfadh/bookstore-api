<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function testRegisterSuccess()
    {
        $this->post('/api/register', [
            'email' => 'kunan@gmail.com',
            'password' => 'rahasia',
            'name' => 'Kunan Fadh'
        ])->assertStatus(201)
            ->assertJson([
                "data" => [
                    'name' => 'Kunan Fadh',
                    'email' => 'kunan@gmail.com',
                    'role' => 'customer',
                ]
            ]);
    }

    public function testRegisterFailed()
    {
        $this->post('/api/register', [
            'email' => '',
            'password' => '',
            'name' => ''
        ])->assertStatus(400)
            ->assertJson([
                "errors" => [
                    'email' => [
                        "The email field is required."
                    ],
                    'password' => [
                        "The password field is required."
                    ],
                    'name' => [
                        "The name field is required."
                    ]
                ]
            ]);
    }

    public function testRegisterUsernameAlreadyExists()
    {
        $this->testRegisterSuccess();
        $this->post('/api/register', [
            'email' => 'kunan@gmail.com',
            'password' => 'rahasia',
            'name' => 'Kunan Fadh'
        ])->assertStatus(400)
            ->assertJson([
                "errors" => [
                    'email' => [
                        "The email has already been taken."
                    ]
                ]
            ]);
    }

    public function testLoginSuccess()
    {
        $this->testRegisterSuccess();
        // // $token = JWTAuth::getToken();
        $this->post('/api/login', [
            'email' => 'kunan@gmail.com',
            'password' => 'rahasia',
        ])->assertStatus(201)
            ->assertJson([
                'data' => [
                    'email' => 'kunan@gmail.com',
                    'name' => 'Kunan Fadh'
                ]
            ])
            ->assertJsonStructure([
                'token'
            ]);

        // self::assertNotNull($user->token);
    }

    public function testLoginFailedUsernameNotFound()
    {
        $this->post('/api/login', [
            'email' => 'kunan@gmail.com',
            'password' => 'rahasia',
        ])->assertStatus(401)
            ->assertJson([
                'errors' => [
                    "message" => [
                        "email or password wrong"
                    ]
                ]
            ]);
    }

    public function testLoginFailedPasswordWrong()
    {
        $this->testRegisterSuccess();
        $this->post('/api/login', [
            'email' => 'kunan@gmail.com',
            'password' => 'salah',
        ])->assertStatus(401)
            ->assertJson([
                'errors' => [
                    "message" => [
                        "email or password wrong"
                    ]
                ]
            ]);
    }

    public function testLogoutSuccess()
    {
        $this->testLoginSuccess();
        $user = User::where('email', 'kunan@gmail.com')->first();
        $token = JWTAuth::fromUser($user);
        $this->post(uri: '/api/logout', headers: [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $token
        ])->assertStatus(200)
            ->assertJson([
                "data" => [],
                'message' => 'Logout Success!',
            ]);

    }

    public function testLogoutFailed()
    {
        $this->testLoginSuccess();
        $token = '12345';
        $this->post(uri: '/api/logout', headers: [
            'Authorization' => 'Bearer ' . $token
        ])->assertStatus(401);
    }

    public function testProfileSuccess()
    {
        $this->testLoginSuccess();
        $user = User::where('email', 'kunan@gmail.com')->first();
        $token = JWTAuth::fromUser($user);
        $this->get(uri: '/api/profile', headers: [
            'Authorization' => 'Bearer ' . $token
        ])->assertStatus(200)
            ->assertJson([
                "data" => [
                    'name' => 'Kunan Fadh',
                    'email' => 'kunan@gmail.com',
                    'role' => 'customer',
                ]
            ]);
    }

    public function testUpdateSuccess()
    {
        $this->testLoginSuccess();
        $user = User::where('email', 'kunan@gmail.com')->first();
        $token = JWTAuth::fromUser($user);
        $this->put('/api/users/update/'.$user->id, [
            'name' => 'Kunan F'
        ], headers: [
            'Authorization' => 'Bearer ' . $token
        ])->assertStatus(200)
            ->assertJson([
                "data" => [
                    'name' => 'Kunan F',
                    'email' => 'kunan@gmail.com',
                    'role' => 'customer',
                ]
            ]);
    }

    public function testListUserSuccess()
    {
        $this->seed(UserSeeder::class);
        $response = $this->post('/api/login', [
            'email' => 'admin@gmail.com',
            'password' => 'password',
        ])->decodeResponseJson();

        if (!isset($response['token'])) {
            $this->fail('Token not found in response');
        }

        $token = $response['token'];

        $this->get('/api/users/list', [
            'Authorization' => 'Bearer ' . $token
        ])->assertStatus(200);

    }
    public function testListUserUnautorize()
    {
        $this->testLoginSuccess();
        $user = User::where('email', 'kunan@gmail.com')->first();
        $token = JWTAuth::fromUser($user);
        $this->get('/api/users/list', headers: [
            'Authorization' => 'Bearer ' . $token
        ])->assertStatus(403);
    }

}
