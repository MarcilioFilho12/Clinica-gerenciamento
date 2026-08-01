<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * Gate PRG Fase 1 — checks estáticos / validação (sem tenant completo).
 */
class PrgPhase1SecurityTest extends TestCase
{
    public function test_login_route_uses_throttle_middleware(): void
    {
        $login = collect(app('router')->getRoutes())->first(
            fn ($r) => in_array('POST', $r->methods(), true) && $r->uri() === 'api/auth'
        );

        $this->assertNotNull($login);
        $this->assertTrue(collect($login->gatherMiddleware())->contains(
            fn ($m) => is_string($m) && str_starts_with($m, 'throttle:login')
        ));
    }

    public function test_logo_upload_rejects_svg_mime(): void
    {
        $validator = Validator::make(
            ['logo' => UploadedFile::fake()->create('evil.svg', 100, 'image/svg+xml')],
            ['logo' => 'required|image|mimes:jpeg,jpg,png,gif,webp|max:2048']
        );

        $this->assertTrue($validator->fails());
    }

    public function test_logo_upload_accepts_png(): void
    {
        $validator = Validator::make(
            ['logo' => UploadedFile::fake()->image('logo.png')],
            ['logo' => 'required|image|mimes:jpeg,jpg,png,gif,webp|max:2048']
        );

        $this->assertFalse($validator->fails());
    }
}
