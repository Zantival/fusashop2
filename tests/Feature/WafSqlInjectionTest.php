<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WafSqlInjectionTest extends TestCase
{
    use RefreshDatabase;
    public function test_sql_injection_payloads_are_blocked_immediately_on_auth_routes(): void
    {
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);
        // 1. Time-based SQL injection probe on /login
        $resLogin = $this->post('/login', [
            'email' => "admin' OR SLEEP(5)--",
            'password' => 'secret123',
        ]);
        $resLogin->assertStatus(400);

        // 2. Boolean-based SQL injection probe on /forgot-password
        $resForgot = $this->post('/forgot-password', [
            'email' => "user' OR '1'='1",
        ]);
        $resForgot->assertStatus(400);

        // 3. Stacked query on /register
        $resRegister = $this->post('/register', [
            'email' => "test'; DROP TABLE users;--",
            'name' => 'Attacker',
        ]);
        $resRegister->assertStatus(400);

        // 4. Normal login validation returns regular validation redirect (not 400)
        $resNormal = $this->post('/login', [
            'email' => 'normal.user@example.com',
            'password' => 'invalid-password',
        ]);
        $resNormal->assertStatus(302);
    }
}
