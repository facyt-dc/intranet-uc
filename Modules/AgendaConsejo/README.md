# Módulo: Agenda de Consejos

Este módulo permite la gestión digital de los Consejos de Departamento.
Para Utilizar, simplemente utilize los roles de Director o usuario dependiendo de la necesidad.

## Funcionalidades Principales
1.  **Gestión de Agendas:** Creación de consejos con fecha, director y participantes.
2.  **Puntos de Discusión:** Gestión dinámica de los puntos a tratar.
3.  **Votación:** Sistema de votos (A favor, En contra, etc.) configurable por punto. Solo los consejeros habilitados pueden votar.
4.  **Conclusiones:** Campo de texto para cerrar un punto, editable solo por el Director tras la votación.
5.  **Comentarios:** Foro de discusión por punto para los participantes.

## Roles y Permisos (Spatie)
*   `Director`: Puede crear agendas, definir puntos, asignar votantes, cerrar la agenda y escribir conclusiones.
*   `Consejero`: Puede ver agendas asignadas, comentar y votar en los puntos donde fue habilitado.

## Flujo de Estados
*   `Programado`: Agenda creada.
*   `En Votación`: (Implícito) Puntos abiertos.
*   `Cerrado`: Agenda finalizada. No se permiten ediciones, nuevos votos ni comentarios.

## Instalación Individual
Si se despliega este módulo por separado:
1.  Correr migraciones: `php artisan module:migrate AgendaConsejo`
2.  Correr seeders: `php artisan module:seed AgendaConsejo`

En caso contrario, viene con el proyecto automaticamente.