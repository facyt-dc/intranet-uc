# Arquitectura del Sistema

El proyecto utiliza una arquitectura de **Monolito Modular** basada en el paquete `nwidart/laravel-modules`: https://laravelmodules.com/docs/11/getting-started/introduction.

Para la permisologia se utilizo spate/laravel-permission: https://spatie.be/docs/laravel-permission/v6/introduction.

Las traducciones no utilizan el estandar de lavarel sino que se implemento i18n, mas detalles en  **[Frontend e i18n](docs/FRONTEND_GUIDE.md)**.

las vistas de los modulos se pueden servir como indica la documentacion de `nwidart/laravel-modules`, pero de manera interna ser sirven con mecanismos personalizados debido a las particularidades del proyecto. Mas detalles en **[Frontend e i18n](docs/FRONTEND_GUIDE.md)**.

## Estructura de Directorios
```text
/
├── app/                 <-- CORE: Modelos globales (User), Auth, lógica base.
├── Modules/             <-- MÓDULOS: Funcionalidades independientes.
│   └── <Module>/   <-- Módulo con funcionalidad especifica.
│       ├── Database/    <-- Migraciones y Seeders del módulo.
│       ├── app/        <-- Controladores, eventos, Middleware del módulo.
│       ├── Models/      <-- Modelos específicos (Agenda, Vote, etc).
│       └── resources/   <-- Vistas (React) y Traducciones (JSON) del módulo.
```

## Desacoplamiento (Inversión de Control)
Se debe mantener la arquitectura de manera que **el Core (`app/`) no debe depender directamente de los Módulos**.

### Inyección de Relaciones en `User`
El modelo `App\Models\User` no tiene métodos harcodeados referenciando a `Modules\<Module>`. En su lugar, las relaciones se inyectan dinámicamente en el `boot()` del `<Module>ServiceProvider`:

```php
// Modules/<Module>/Providers/<Module>ServiceProvider.php
Modelo::resolveRelationUsing('<Field>', function ($userModel) {
    return $userModel->hasMany(Modelo::class);
});
```

Esto permite que si se elimina el módulo de Agenda, el modelo de Usuario siga funcionando sin errores.

## Seeders
En `DatabaseSeeder.php` base se debe agregar una llamada dinámica para ejecutar los seeders de los módulos solo si estos existen y están habilitados, evitando errores de "Class not found".

Ejemplo con el modulo AgendaConsejo:

```php
$agendaModule = Module::find('AgendaConsejo');
if ($agendaModule && $agendaModule->isEnabled()) {
    $this->call("Modules\\AgendaConsejo\\Database\\Seeders\\AgendaConsejoDatabaseSeeder");
}
```

## Comunicacion entre modulos
El siguiente video explica como se pueden comunicar modelos de dos maneras:
- Event/Listener para eventos
- Servi

**Video:**
https://www.youtube.com/watch?v=NNRWCris2u0