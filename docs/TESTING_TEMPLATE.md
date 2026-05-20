# Plantillas de Tests — Intranet UC

Guía y plantillas para escribir tests Feature en los módulos de Intranet UC.
Antes de escribir tests, leer las reglas en [`MODULES_COOKBOOK.md`](MODULES_COOKBOOK.md), secciones 5.8–5.12.

---

## 1. Tipos de tests a escribir por módulo

Apuntar al **flujo crítico** del módulo, no a cobertura del 100%.

1. **Smoke tests** — las rutas principales (index, show) responden sin error 500.
2. **Tests de autorización** — usuarios sin el rol correcto reciben 403; no autenticados son redirigidos a `/login`.
3. **Tests de CRUD happy path** — crear, actualizar y eliminar con datos válidos devuelven redirect con éxito y el cambio aparece en la base de datos.
4. **Tests de validación** — datos inválidos (campo vacío, tipo incorrecto) devuelven 422.
5. **Tests de lógica de negocio** — reglas específicas del módulo (ej. un consejero no puede votar dos veces, un punto sin votos mínimos no puede cerrarse).

Mínimo requerido al integrar un módulo: un test de cada tipo 1, 2 y 3.

---

## 2. Plantilla base: tests CRUD

```php
<?php

namespace Modules\<Nombre>\Tests\Feature;

use App\Models\User;
use Modules\<Nombre>\Models\<Modelo>;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class <Modelo>CrudTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
    }

    public function test_admin_can_list_recursos(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('<recurso>.index'));

        $response->assertOk();
    }

    public function test_admin_can_create_recurso(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('<recurso>.store'), [
                // Proveer todos los campos requeridos por el FormRequest
                'campo_requerido' => 'valor de prueba',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('<tabla>', [
            'campo_requerido' => 'valor de prueba',
        ]);
    }

    public function test_admin_can_update_recurso(): void
    {
        $recurso = <Modelo>::factory()->create();

        $response = $this->actingAs($this->admin)
            ->put(route('<recurso>.update', $recurso), [
                'campo_requerido' => 'valor actualizado',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('<tabla>', ['campo_requerido' => 'valor actualizado']);
    }

    public function test_admin_can_delete_recurso(): void
    {
        $recurso = <Modelo>::factory()->create();

        $response = $this->actingAs($this->admin)
            ->delete(route('<recurso>.destroy', $recurso));

        $response->assertRedirect();
        $this->assertDatabaseMissing('<tabla>', ['id' => $recurso->id]);
    }
}
```

---

## 3. Plantilla: tests de autorización

```php
<?php

namespace Modules\<Nombre>\Tests\Feature;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class <Modelo>AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'otro_rol', 'guard_name' => 'web']);
    }

    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $response = $this->get(route('<recurso>.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_user_without_role_cannot_access(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('<recurso>.index'));

        $response->assertForbidden();
    }

    public function test_user_with_wrong_role_cannot_create(): void
    {
        $user = User::factory()->create();
        $user->assignRole('otro_rol');

        $response = $this->actingAs($user)
            ->post(route('<recurso>.store'), []);

        $response->assertForbidden();
    }
}
```

---

## 4. Plantilla: tests de lógica de negocio

Para flujos con varias entidades relacionadas (como votación, cambios de estado, etc.):

```php
<?php

namespace Modules\<Nombre>\Tests\Feature;

use App\Models\User;
use Modules\<Nombre>\Models\<ModeloPrincipal>;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class <Flujo>FlowTest extends TestCase
{
    use RefreshDatabase;

    protected User $actor;
    protected <ModeloPrincipal> $recurso;

    protected function setUp(): void
    {
        parent::setUp();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Role::firstOrCreate(['name' => 'rol_actor', 'guard_name' => 'web']);

        $this->actor = User::factory()->create();
        $this->actor->assignRole('rol_actor');

        // Crear entidades relacionadas con TODOS sus campos NOT NULL
        $this->recurso = <ModeloPrincipal>::create([
            'campo_not_null' => 'valor',
            // ... todos los campos requeridos por la migración
        ]);
    }

    public function test_actor_can_realizar_accion(): void
    {
        $response = $this->actingAs($this->actor)
            ->post(route('<accion>.ruta', $this->recurso), [
                'parametro' => 'valor',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('<tabla_resultado>', [
            'campo' => 'valor esperado',
        ]);
    }

    public function test_actor_no_puede_repetir_accion(): void
    {
        // Primera acción
        // ...

        // Segunda acción (debe fallar)
        $response = $this->actingAs($this->actor)
            ->post(route('<accion>.ruta', $this->recurso), []);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }
}
```

---

## 5. Reglas para escribir tests

- **No usar doc-comments `/** @test */`**. Usar el prefijo `test_` en el nombre del método. Más simple y no genera warnings en PHPUnit 11+.
- **`setUp()` debe llamar a `parent::setUp()` como primera línea.**
- **Limpiar caché de Spatie al inicio de cada `setUp()`:**
  ```php
  app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
  ```
- **Si un test es complejo y necesita revisión humana**, marcarlo con `$this->markTestSkipped('Razón clara aquí')` en lugar de forzar aserciones falsas.
- **Cada test debe verificar una sola cosa.** Un test que falla debe identificar exactamente qué está roto.
- **Usar factories siempre que existan.** Si el factory del modelo no existe aún, crearlo antes de escribir el test.
- **Cubrir TODOS los campos NOT NULL** en factories y en llamadas directas a `Model::create()` dentro de los tests (ver Regla 5.11 del cookbook).
- **`Role::firstOrCreate()`** en lugar de `Role::create()` para evitar errores de duplicado si se reusan los mismos nombres de rol entre tests.

---

## 6. Ejemplos reales del proyecto

Tests de referencia escritos para `AgendaConsejo`:

- [`Modules/AgendaConsejo/tests/Feature/AgendaCrudTest.php`](../Modules/AgendaConsejo/tests/Feature/AgendaCrudTest.php) — CRUD de Agenda con roles director/counselor
- [`Modules/AgendaConsejo/tests/Feature/AgendaAuthorizationTest.php`](../Modules/AgendaConsejo/tests/Feature/AgendaAuthorizationTest.php) — autorización por rol
- [`Modules/AgendaConsejo/tests/Feature/VotingFlowTest.php`](../Modules/AgendaConsejo/tests/Feature/VotingFlowTest.php) — flujo de votación con validación de voto único

---

## 7. Correr los tests de un módulo

```bash
# Solo el módulo
php artisan test --filter=AgendaConsejo

# Suite completa (debe seguir en verde)
php artisan test
```
