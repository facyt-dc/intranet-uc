# Roadmap del Proyecto

Este documento detalla el plan de trabajo actual, las tareas de mantenimiento pendientes y la visión a futuro para la arquitectura modular de **Intranet UC**.

---

## 🚀 Fase 1: Estabilidad y Preparación para Entrega (Prioridad Alta)
*Objetivo: Asegurar que el proyecto sea estable, limpio y fácil de instalar para evaluación.*

- [ ] **Experiencia de Instalación ("Professor-Ready"):**
    - [ ] Verificar que `composer install` y `npm install` funcionen sin conflictos.
    - [ ] Probar que el comando `php artisan migrate:refresh --seed` ejecute tanto migraciones del Core como de los Módulos sin errores.
    - [ ] Asegurar que el `.env.example` contenga todas las variables necesarias (incluyendo configuración de `MAIL_` para Mailpit/Log).

- [ ] **Corrección de Bugs Críticos:**
    - [ ] **Gestión de Roles:** Solucionar el bug que impide revocar/quitar roles a los usuarios existentes desde la interfaz de administración.

- [ ] **Limpieza de Código y Estructura:**
    - [ ] **Traducciones:** Eliminar carpetas residuales en `resources/lang` (y en los módulos) que no se estén utilizando, ya que el sistema migró a archivos JSON en `resources/assets/locales`.
    - [ ] **Vistas:** Verificar y eliminar vistas Blade no utilizadas si todo el frontend está en Inertia/React.

---

## 🛡 Fase 2: Seguridad y Permisología (Spatie)
*Objetivo: Estandarizar el manejo de roles siguiendo las mejores prácticas.*

- [ ] **Refactorización de Seeders de Permisos:**
    - [ ] Revisar documentación de `spatie/laravel-permission`, para definir las buenas practicas.
    - [ ] Asegurar que los seeders limpien la caché de permisos (`app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();`) antes de crear nuevos datos.
    - [ ] Implementar un enfoque de "Permisos granulares" vs "Chequeo de Roles" donde aplique.

- [ ] **Definición Formal de Roles:**
    - [ ] Documentar y definir estrictamente los roles del sistema (ej: `SuperAdmin`, `JefeDepartamento`, `Director`, `Consejero`, `Secretaria`).
    - [ ] Definir la matriz de acceso: ¿Qué puede hacer exactamente cada rol en el Core y en los Módulos?

---

## 🏗 Fase 3: Arquitectura y Refactorización Modular
*Objetivo: Mejorar la implementación de `nwidart/laravel-modules` y reducir deuda técnica.*

- [ ] **Migración a `Develop` (Integración):**
    - [ ] Convertir las funcionalidades restantes que aún viven en `app/`, a sus propios Módulos independientes.
    - [ ] Realizar el merge de las ramas de características (feature branches) a la rama `develop`.

---

## 📦 Fase 4: Nuevos Módulos (Futuro)

Documentar que modulos se deben hacer.