<?php

namespace Tests\Unit;

use App\Custom\Jwt;
use Tests\TestCase;

class JwtTtlTest extends TestCase
{
    public function test_default_ttl_is_four_hours(): void
    {
        config(['jwt.ttl_seconds' => 14400]);

        $this->assertSame(14400, Jwt::ttlSeconds());
    }

    public function test_ttl_enforces_minimum_five_minutes(): void
    {
        config(['jwt.ttl_seconds' => 60]);

        $this->assertSame(300, Jwt::ttlSeconds());
    }

    public function test_ttl_respects_configured_value(): void
    {
        config(['jwt.ttl_seconds' => 7200]);

        $this->assertSame(7200, Jwt::ttlSeconds());
    }
}
