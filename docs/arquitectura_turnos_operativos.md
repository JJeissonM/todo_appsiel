# Turnos operativos en Appsiel 1.0

## Decisión de arquitectura

`core_turnos_operativos` es la fuente de verdad de una sesión operacional. Su
`fecha_operativa` no cambia cuando el cierre ocurre al día siguiente. La pertenencia
de documentos y movimientos nuevos se expresa con `turno_operativo_id`; nunca se
reescribe `created_at` para asignar un registro al turno.

La activación es opt-in mediante `core_turno_configuraciones`. La resolución usa,
en este orden, la configuración de contexto (`pdv`), la del módulo y la global de
empresa. La ausencia de una fila equivale a `TRADICIONAL`. Ejemplo para activar un
PDV:

```sql
INSERT INTO core_turno_configuraciones
    (core_empresa_id, modulo, contexto_tipo, contexto_id, modo, created_at, updated_at)
VALUES
    (1, 'ventas_pos', 'pdv', 3, 'TURNOS', NOW(), NOW()),
    (1, 'tesoreria', 'pdv', 3, 'TURNOS', NOW(), NOW()),
    (1, 'inventarios', 'pdv', 3, 'TURNOS', NOW(), NOW()),
    (1, 'hotel', 'pdv', 3, 'TURNOS', NOW(), NOW());
```

También puede activarse toda la empresa con `modulo='*'`, `contexto_tipo='*'` y
`contexto_id=0`. Se recomienda activar juntos los módulos que participan en una
misma operación. Para activar todos los módulos de un solo PDV puede usarse
`modulo='*'`, `contexto_tipo='pdv'` y el ID del PDV.

## Ciclo de vida y propagación

La apertura POS histórica crea un turno `ABIERTO`; el cierre enlaza el mismo turno
y lo lleva a `CERRADO`, aun si sucede después de medianoche. Una clave única impide
dos turnos abiertos simultáneos en el mismo PDV, pero no impide varios turnos
secuenciales en una fecha operativa.

`TurnoContext` permite propagar un turno dentro de una operación compuesta. Los
modelos integrados también lo recuperan por la identidad exacta del documento
(empresa, transacción, tipo y consecutivo), no por fechas. Los ajustes sobre turnos
cerrados se realizan con `TurnoManager::assignAdjustment`, que exige motivo y crea
un evento de auditoría. Las reaperturas también exigen motivo.
El ciclo adicional `CERRADO -> AUDITANDO -> AUDITADO` se expone mediante
`startAudit` y `completeAudit`.

## Integrado inicialmente

- Aperturas y cierres POS.
- Facturas POS y documentos de ventas estándar/FE.
- Encabezados y movimientos de inventario.
- Encabezados, movimientos y arqueos de tesorería.
- Pedidos hoteleros y sus facturas; una estadía no queda ligada a un solo turno.
- Reporte base de movimientos de caja por FK de turno.

## Compatibilidad y deuda histórica

Sin configuración, continúan sin cambios los filtros por fecha/hora, la apertura y
cierre diarios y los reportes existentes. El parche que sincroniza `created_at` de
Tesorería con cierres se conserva sólo en modo tradicional. Los registros históricos
permanecen con `turno_operativo_id = NULL`; no se hace backfill incierto.

Siguen pendientes de migración progresiva los reportes especializados que llaman
directamente consultas históricas, contabilidad/CxC/CxP, compras, producción,
restaurantes fuera del flujo POS y nómina. Al migrarlos deben recibir el contexto o
la FK, no reconstruir el turno con rangos horarios.
