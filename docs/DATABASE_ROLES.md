# Base de Datos Global y Permisología

Este documento detalla el esquema de base de datos del **Núcleo (Core)** del sistema y la estrategia general de seguridad implementada mediante `spatie/laravel-permission`.

## 1. Gestión de Usuarios (Modelo `User`)

El modelo `App\Models\User` es la entidad central del sistema. Debido a la arquitectura modular, este modelo se mantiene **desacoplado**:

*   **Sin Relaciones Directas:** El archivo `User.php` no contiene métodos que relacionen directamente al usuario con tablas de módulos (como `agendas` o `votes`).
*   **Inyección Dinámica:** Las relaciones con los módulos se inyectan en tiempo de ejecución (`runtime`) mediante el `ServiceProvider` de cada módulo usando `resolveRelationUsing`.

Esto garantiza que el Core del sistema pueda funcionar independientemente de qué módulos estén activos o instalados.

## 2. Sistema de Roles y Permisos

El proyecto utiliza el paquete `spatie/laravel-permission` para gestionar el control de acceso (ACL).

### Estrategia de Autorización
El sistema utiliza una estrategia basada principalmente en **Roles** (`RBAC`) complementada con **Lógica de Negocio** en los controladores.

1.  **Middleware de Rutas:** Se utiliza para restringir el acceso a grupos enteros de rutas.
    *   Ejemplo: `Route::middleware(['role:Director'])`
2.  **Lógica en Controladores:** Se verifican condiciones específicas más allá del rol simple.
    *   Ejemplo: Un usuario puede tener el rol `Consejero`, pero el controlador verificará si pertenece específicamente al consejo que intenta ver.

### Roles Globales Definidos

Los siguientes roles se siembran en la base de datos mediante `DatabaseSeeder.php` y están disponibles para ser usados por cualquier módulo:

| Rol | Slug | Descripción General |
| :--- | :--- | :--- |
| **Admin** | `admin` | Acceso total al sistema y configuración global. |
| **Director** | `director` | Perfil administrativo/gerencial. Capacidad de crear recursos, gestionar procesos y cerrar flujos de trabajo. |
| **Teacher** | `teacher` |
| **Administrative** | `administrative` |
| **Consejero** | `Consejero` | Perfil participativo. Capacidad de lectura, interacción (votos/comentarios) y edición limitada a sus propios registros. |

> **Importante:** La asignación de roles se realiza a nivel global. Sin embargo, el *comportamiento* de ese rol puede variar dependiendo del contexto del módulo en el que se encuentre el usuario.

---

## 4. Migraciones del Core

Las tablas base del sistema se encuentran en `database/migrations/` y son esenciales para el arranque de la aplicación.

Cualquier migración específica de funcionalidad (como tablas de agendas, inventario, etc.) **debe** residir dentro de la carpeta `Database/Migrations` de su respectivo módulo.