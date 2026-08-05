<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Garante que as novas rotas do ciclo de vida de consultas exigem autenticação
 * (jwt) e o perfil correto (profile:admin,recepcao,profissional), e que as
 * rotas de segmento fixo (vencidas/dashboard) foram registradas ANTES de
 * consultas/{id} — senão o Laravel casaria "vencidas"/"dashboard" como {id}.
 */
class ConsultaStatusRoutesTest extends TestCase
{
    private function routeFor(string $method, string $uri)
    {
        return collect(app('router')->getRoutes())->first(
            fn ($r) => in_array($method, $r->methods(), true) && $r->uri() === $uri
        );
    }

    public function test_novas_rotas_de_ciclo_de_vida_estao_registradas(): void
    {
        $rotas = [
            ['POST', 'api/consultas/{id}/transferir'],
            ['POST', 'api/consultas/{id}/reagendar'],
            ['POST', 'api/consultas/{id}/no-show'],
            ['GET', 'api/consultas/{id}/historico'],
            ['GET', 'api/consultas/vencidas'],
            ['GET', 'api/consultas/dashboard'],
        ];

        foreach ($rotas as [$method, $uri]) {
            $this->assertNotNull($this->routeFor($method, $uri), "Rota {$method} {$uri} não encontrada.");
        }
    }

    public function test_novas_rotas_exigem_jwt_e_perfil_clinico(): void
    {
        $rotas = [
            ['POST', 'api/consultas/{id}/transferir'],
            ['POST', 'api/consultas/{id}/reagendar'],
            ['POST', 'api/consultas/{id}/no-show'],
            ['GET', 'api/consultas/{id}/historico'],
            ['GET', 'api/consultas/vencidas'],
            ['GET', 'api/consultas/dashboard'],
        ];

        foreach ($rotas as [$method, $uri]) {
            $route = $this->routeFor($method, $uri);
            $middleware = collect($route->gatherMiddleware());

            $this->assertTrue($middleware->contains('jwt'), "{$uri} deveria exigir jwt.");
            $this->assertTrue(
                $middleware->contains(fn ($m) => is_string($m) && str_starts_with($m, 'profile:')),
                "{$uri} deveria exigir um profile permitido."
            );
        }
    }

    public function test_rotas_de_segmento_fixo_vem_antes_do_wildcard_id(): void
    {
        $routes = collect(app('router')->getRoutes()->getRoutes());

        $posicaoVencidas = $routes->search(fn ($r) => in_array('GET', $r->methods(), true) && $r->uri() === 'api/consultas/vencidas');
        $posicaoDashboard = $routes->search(fn ($r) => in_array('GET', $r->methods(), true) && $r->uri() === 'api/consultas/dashboard');
        $posicaoShow = $routes->search(fn ($r) => in_array('GET', $r->methods(), true) && $r->uri() === 'api/consultas/{id}');

        $this->assertNotFalse($posicaoVencidas);
        $this->assertNotFalse($posicaoDashboard);
        $this->assertNotFalse($posicaoShow);
        $this->assertLessThan($posicaoShow, $posicaoVencidas, 'consultas/vencidas precisa vir antes de consultas/{id}.');
        $this->assertLessThan($posicaoShow, $posicaoDashboard, 'consultas/dashboard precisa vir antes de consultas/{id}.');
    }
}
