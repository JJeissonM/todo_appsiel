# Turnos operativos: alcance inicial simplificado

## Objetivo

La implementación inicial está orientada a hoteles pequeños y medianos. Su propósito es relacionar explícitamente cada hecho operativo con la apertura y el cierre que le corresponden mediante `turno_operativo_id`, sin reconstruir esa relación por rangos de fecha/hora ni modificar `created_at`.

## Reglas del piloto

- La empresa tiene una sola configuración efectiva: `TRADICIONAL` o `TURNOS`.
- En `TURNOS` sólo puede existir un turno abierto por empresa.
- El PDV de la apertura se conserva como información descriptiva y para compatibilidad con el flujo histórico; no divide el contexto operativo de la empresa.
- En el formulario de una transacción, un administrador puede seleccionar un turno o dejarlo vacío cuando se trate de una operación administrativa.
- Para los roles de cajero configurados en `turnos.turn_selection_locked_roles`, el sistema asigna automáticamente el turno abierto por ese usuario. El cajero no puede cambiarlo ni quitarlo.
- Antes de enviar una transacción, el navegador consulta por AJAX la validez del turno y valida localmente el motivo requerido para los ajustes y la asignación automática del cajero. Los errores se muestran dentro del formulario sin recargarlo ni perder líneas o valores digitados; el backend conserva las mismas reglas como control definitivo.
- Los derivados técnicos conservan el turno persistido en su documento origen. Que la selección sea opcional para un administrador no permite borrar o contradecir un turno ya propagado.
- Un turno seleccionado debe existir, pertenecer a la empresa y cumplir las reglas de estado. Los ajustes sobre turnos cerrados mantienen sus validaciones y trazabilidad.
- Una empresa sin configuración continúa en `TRADICIONAL` y admite movimientos históricos con `turno_operativo_id = NULL`.

## Horarios fijos

Un horario habitual no debe representarse reutilizando siempre la misma fila de turno. Cada apertura crea una instancia operativa independiente con sus horas reales; así, las transacciones conservan la apertura y el cierre exactos aun cuando existan retrasos, extensiones de estadía o cruces de medianoche.

Los horarios fijos pueden seguir definidos en el PDV como valores predeterminados para facilitar la apertura. No se introduce otro catálogo de plantillas de turno en esta fase.

## Persistencia mínima conservada

- `core_turno_configuraciones`: activación opt-in por empresa. El servicio normaliza el alcance a `* / * / 0`.
- `core_turnos_operativos`: instancia real de cada apertura y cierre; es la FK usada por las transacciones.
- `core_turno_eventos`: trazabilidad de apertura, cierre, reapertura, auditoría y ajustes.

No se eliminan estas tablas porque cumplen responsabilidades distintas. Tampoco se hace backfill ni se borran configuraciones granulares creadas durante QA. Mientras no exista la fila global, una activación granular previa se interpreta de manera compatible como activación para toda la empresa; al guardar desde el CRUD simplificado se crea o actualiza la fila global.

## Activación

1. Cerrar cualquier apertura o turno que continúe abierto.
2. En Configuración de turnos, seleccionar `TURNOS` para la empresa.
3. Abrir el primer turno desde el flujo de apertura existente.
4. Verificar que el cajero vea el turno asignado automáticamente.
5. Verificar que un administrador pueda escoger un turno o dejarlo vacío.
6. Comprobar en los documentos y sus impresiones el código, fecha operativa y estado del turno cuando exista FK.

Para volver a `TRADICIONAL`, primero deben cerrarse todos los turnos abiertos de la empresa.
