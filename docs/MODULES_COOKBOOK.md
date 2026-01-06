# Manual de Módulos (Cookbook)

Guía para desarrolladores sobre cómo crear y gestionar módulos en Intranet UC.

## Crear un Nuevo Módulo

1.  **Generar estructura:**
    ```bash
    php artisan module:make NombreModulo
    ```

2.  **Limpiar configuración:**
    Hemos desactivado la generación automática de carpetas `views` y `lang` en `config/modules.php`. Sin embargo, debes revisar el `ServiceProvider` generado y eliminar referencias a `$this->registerViews()` si usas Inertia.

3.  **Acomodar estructura de carpetas:**
    Si la estructura de carpetas generada, no es acorde a la estructura generada para el proyecto entonces ajustar. TODO: verificar si la generacion es correcta, sino corregir los stubs.

4.  **Ejecutar `composer dump-autoload`**

4.  **Al referenciar vistas:**
    En los controlers referenciar vistasa usando la sintaxis `<module>::<view>`.

5.  **Al crear seeders:**
    Agregar la llamada del seeder del modulo al seeder principal condicionalmente, para mas detalles revisar **[Arquitectura Modular](docs/ARCHITECTURE.md):**.

## Adaptar una Funcionalidad Antigua a un Módulo

Si necesitas, mover código de `app/` a `Modules/<modulo>/app`:

1.  **Mover Archivos:**
    Mover archivos a su respectiva carpeta equivalente en el modulo. Por ejemplo:

    *   Controladores -> `Modules/<modulo>/app/Http/Controllers`
    *   Modelos -> `Modules/<modulo>/app/Models`
    *   Migraciones -> `Modules/<modulo>/Database/Migrations`
    *   Traducciones -> `Modules/<modulo>/resources/assets/js/i18n`

2.  **Actualizar Namespaces:**
    Cambia `App\...` por `Modules\<modulo>\...` en todos los archivos movidos.
    
3.  **Actualizar Referencias:**
    En los controllers cambia `Inertia:render("<view>")` a `Inertia:render("<modulo>::<view>")`

4.  **Mover seeders:**
    Agregar la llamada del seeder del modulo al seeder principal condicionalmente, para mas detalles revisar **[Arquitectura Modular](docs/ARCHITECTURE.md):**.

5.  **Inyectar Relaciones:**
    No edites `User.php`. Define las relaciones en el `boot()` del `ServiceProvider` del nuevo módulo (ver `ARCHITECTURE.md`).

6.  **Rutas:**
    Mueve las rutas de `routes/web.php` a `Modules/<module>/Routes/web.php`.

## Comandos Útiles
*   `php artisan module:list`: Ver módulos activos.
*   `php artisan module:seed AgendaConsejo`: Correr seeders de un módulo específico.
*   `php artisan module:publish`: Publicar assets/config.