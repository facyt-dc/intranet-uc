# Reporte de Fase 0.5 — Cierre de tests

**Fecha:** 2026-05-20
**Rama:** develop
**Commit HEAD antes de iniciar:** 89fbaa3

---

## Resumen ejecutivo

- Tests del scaffold Auth: 8 fallaban antes / 18 pasan después (0 fallos)
- Tests fantasma de Thesis eliminados: 4 archivos + carpeta completa `Modules/Thesis/`
- Tests de AgendaConsejo: 0 de 13 pasaban antes (no estaban en phpunit.xml) / 13 de 13 pasan después
- Tests marcados como skipped (pendientes de revisión): 0
- Estado general: **VERDE**

---

## PARTE 1 — Vite manifest

- [x] `withoutVite()` agregado en `tests/TestCase.php`
- Tests de Auth que fallaban: 8 (los que renderizan vistas Blade con `@vite(...)`)
- Tests de Auth que ahora pasan: 18/18
- Fallos restantes: ninguno

**Causa:** Las vistas Blade de Breeze/Inertia llaman a `@vite(...)` al renderizarse. En el
entorno de testing no existe `public/build/manifest.json`, causando un 500. Llamar a
`$this->withoutVite()` en el `setUp()` base reemplaza la directiva por strings vacíos.

---

## PARTE 2 — Tests fantasma de Thesis

- [x] Confirmado que módulo Thesis no existe en develop (no aparece en `modules_statuses.json`)
- La carpeta `Modules/Thesis/` contenía **únicamente** los 4 archivos de test (sin código fuente)
- Archivos eliminados:
  - [x] `ThesisStudentCrudTest.php`
  - [x] `ThesisAuthorizationTest.php`
  - [x] `StudentStatusFlowTest.php`
  - [x] `GanttChartTest.php`
- Carpetas eliminadas: `Modules/Thesis/tests/Feature/`, `Modules/Thesis/tests/`, `Modules/Thesis/`

**Nota:** Estos tests se restaurarán cuando se integre la rama `feature/thesis-module` a develop.

---

## PARTE 3 — Tests de AgendaConsejo

### Problema previo al diagnóstico

Los tests de AgendaConsejo **no estaban registrados en `phpunit.xml`**. La suite solo incluía
`tests/Unit` y `tests/Feature`. Se agregó `Modules/AgendaConsejo/tests/Feature` a la testsuite
Feature en `phpunit.xml` como primer paso.

### Diagnóstico inicial

| Test | Categoría | Causa |
|------|-----------|-------|
| `VotingFlowTest::test_counselor_can_cast_vote_on_point` | A | `agenda_points.requested_by_user_id` NOT NULL no cubierto en `setUp()` |
| `VotingFlowTest::test_counselor_cannot_vote_twice_on_same_point` | A | Ídem |
| `VotingFlowTest::test_director_cannot_vote` | A | Ídem |
| `VotingFlowTest::test_vote_is_persisted_in_database` | A | Ídem |

Los tests de `AgendaCrudTest` y `AgendaAuthorizationTest` pasaron sin modificaciones.

### Arreglos aplicados

#### Factories actualizados (Categoría A)
No se crearon ni modificaron factories. El fallo estaba en el `setUp()` del test, no en factories.

#### Corrección en VotingFlowTest (Categoría A)
- [x] `VotingFlowTest.php` — agregado `'requested_by_user_id' => $this->counselor->id` al
  `AgendaPoint::create()` dentro del `setUp()`. Este campo es NOT NULL en la migración
  `create_agenda_points_table` pero no estaba incluido en la llamada de creación directa.

#### Rutas corregidas (Categoría B)
- No aplica. Las rutas usadas en los tests (`agendas.index`, `agendas.store`, `points.votes.store`,
  etc.) coinciden exactamente con los nombres registrados en `Modules/AgendaConsejo/routes/web.php`.

#### Namespaces corregidos (Categoría C)
- No aplica. Los imports de los tests usan `Modules\AgendaConsejo\Models\Agenda` etc., que son
  correctos según el autoload del módulo (`Modules\AgendaConsejo\` → `app/`).

#### Tests marcados como skipped (Categoría D o E)
- Ninguno. Todos los tests resolvieron con correcciones claras.

---

## Resultado final de la suite

```
php artisan test
```

- Tests totales: 38
- Tests pasados: 38
- Tests fallidos: 0
- Tests skipped: 0

Suites que pasan:
- `Tests\Unit\ExampleTest` (1)
- `Tests\Feature\Auth\AuthenticationTest` (4)
- `Tests\Feature\Auth\EmailVerificationTest` (3)
- `Tests\Feature\Auth\PasswordConfirmationTest` (3)
- `Tests\Feature\Auth\PasswordResetTest` (4)
- `Tests\Feature\Auth\PasswordUpdateTest` (2)
- `Tests\Feature\Auth\RegistrationTest` (2)
- `Tests\Feature\ExampleTest` (1)
- `Tests\Feature\ProfileTest` (5)
- `Modules\AgendaConsejo\Tests\Feature\AgendaAuthorizationTest` (4)
- `Modules\AgendaConsejo\Tests\Feature\AgendaCrudTest` (5)
- `Modules\AgendaConsejo\Tests\Feature\VotingFlowTest` (4)

### Tests que aún fallan
Ninguno.

---

## Commits realizados

1. `34ec3e1` — `fix(tests): agrega withoutVite() en TestCase base para evitar Vite manifest error`
2. `fc7f371` — `chore(tests): elimina tests fantasma del módulo Thesis`
3. `0fbdfac` — `fix(tests): corrige tests de AgendaConsejo y agrega suite al phpunit.xml`

---

## Próximo paso

La suite quedó **VERDE** (38/38 tests pasan, 0 skipped).

Listo para avanzar a **Fase 1** (cookbook y reglas del proyecto).
