<?php

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_register(): void
    {
        
    }

    public function test_login(): void
    {
        $user = Usuario::factory()->create([
            'email' => 'santiago@santiago.com',
            'name' => 'Santiago',
        ]);

        $response = $this->post('api/login', [
            'correo' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200);
    }
}