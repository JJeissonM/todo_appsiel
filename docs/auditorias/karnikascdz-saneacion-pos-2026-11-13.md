# Karnikascdz: conciliación de consecutivos POS en producción

Ejecución autorizada por el usuario el 13-11-2026 (America/Bogota) en `u883985631_karnikascdz`. Estado: **COMMITTED**, verificado después del commit y desde una conexión independiente.

Se identificaron tres grupos duplicados en `vtas_pos_doc_encabezados`, por empresa/transacción/tipo/consecutivo. Todas las facturas pertenecen a empresa 1, transacción 47 y tipo 18, prefijo configurado `FV`, y estaban contabilizadas y de contado.

| ID renumerado | ID que conserva número | Anterior | Nuevo | Total de líneas | Recaudo ajustado |
| --- | --- | --- | --- | --- | --- |
| 26816 | 26815 | 26813 | 26814 | $11.130 | $11.100 |
| 28718 | 28717 | 28717 | 28718 | $6.195 | $6.200 |
| 40676 | 40675 | 40675 | 40676 | $8.512,50 | $8.500 |

Los destinos son los números siguientes libres en la misma serie. Se conserva el número de cada grupo en la factura de menor ID.

## Cambios confirmados

| Tabla | Campo | Filas |
| --- | --- | --- |
| `vtas_pos_doc_encabezados` | `consecutivo` | 3 |
| `vtas_pos_movimientos` | `consecutivo` | 3 |
| `vtas_movimientos` | `consecutivo` | 3 |
| `teso_movimientos` | `consecutivo` | 3 |
| `contab_movimientos` | `consecutivo` | 9 |

Total: **21 filas**, únicamente el campo `consecutivo`. Se conservaron importes, fechas, estados, ajustes al peso, IDs y relaciones por ID, inventario y contador. No hay cartera ni abonos para las identidades POS de estos grupos.

## Asignación revisada de movimientos

El primer diagnóstico del comando se detuvo sin modificar datos: los pares comparten empresa, transacción, tipo, número, fecha, usuario, tercero, PDV, cajero y segundo de creación. No se asignaron movimientos por orden de ID ni por hora.

La investigación estableció una asignación única mediante las siguientes evidencias cruzadas:

- Detalles vinculados por ID al encabezado POS y movimientos de ventas vinculados al ID explícito de su remisión. Coinciden productos, cantidades, precios, impuestos y descuentos completos.
- Detalles y movimientos de inventario enlazados a cada remisión corroboran productos y cantidades físicas. La factura 28717 utiliza remisión ID 28752 y la factura 28718 utiliza ID 28751: el orden de IDs no se usó para inferir relaciones.
- Cada encabezado conserva `lineas_registros_medios_recaudos` con el medio, motivo, caja, cuenta bancaria e importe exacto del pago. Se cotejó esa estructura contra la fila de tesorería y contra efectivo recibido menos cambio y total con ajuste al peso. Así se identificó el recaudo con evidencia persistida del propio documento.
- Las líneas contables de ingresos se identificaron por producto/cantidad/crédito frente a los detalles y ventas ya asociados a la remisión. Las líneas de caja coinciden con el recaudo asignado y con la cuenta contable configurada en la caja.
- Los redondeos se identificaron por el ajuste persistido en cada encabezado, signo y cuenta contable verificada: gasto en cuenta ID 385 o ingreso en cuenta ID 246. El conjunto de cada factura conserva equilibrio entre débitos y créditos.

`ownership-proof.json` conserva el detalle de esta prueba, y `reviewed-ownership.json` cada fila completa, propietario y justificación. Se corroboró la asignación de los 41 movimientos POS/ventas/tesorería/contabilidad presentes en las identidades duplicadas, tanto de las facturas que conservan el número como de las renumeradas.

Se invocó la clase del comando Artisan mediante un lanzador privado con base y usuario restringidos a `u883985631_karnikascdz`, y una copia privada del servicio. La adaptación **solo restringe** los candidatos después de superar los filtros originales; exige coincidencia completa con las filas revisadas y agrega al plan las consultas de evidencia de inventario, caja y cuentas. No convierte en válido un candidato rechazado por las condiciones originales ni omite los cotejos de productos, importes y equilibrio del servicio. No se modificó el código del repositorio para esta ejecución.

## Controles

El plan conserva 145 comprobaciones de consultas. Incluye las filas anteriores, esquema, evidencia adicional y todas las tablas base con columnas `consecutivo`; se buscaron los números originales y destinos en campos textuales de descripción, detalle, referencia, documento, observación y concepto del esquema. La búsqueda textual no encontró coincidencias.

Se comprobaron destinos libres, contador suficiente, InnoDB en las tablas intervenidas y ausencia de triggers. El proceso guarda plan, plan inverso y manifiesto SHA-256 antes de escribir, bloquea contadores y filas, revalida el contenido completo, actualiza por IDs explícitos con conteos exactos y compara todas las columnas antes del commit.

Se confirmaron las 21 modificaciones en una sola transacción. La verificación posterior del servicio encontró cero grupos duplicados en el histórico POS. Una conexión independiente repitió las **145 comprobaciones**, cotejó todas las columnas frente al resultado esperado, confirmó que no cambiaron las remisiones y volvió a verificar cero duplicados POS. Evidencia: `independent-verification.json`.

## Anomalía histórica de inventario pendiente

Las remisiones ID **26849 y 26850** comparten empresa 1, transacción de inventario, tipo **7 (`RM`)** y consecutivo **26813**. Los detalles y movimientos de inventario siguen asociados a sus IDs de encabezado y contienen productos diferentes, correspondientes a las dos ventas.

Esta conciliación corresponde a las facturas POS. **No renumera las remisiones ni modifica sus movimientos o costos.** El duplicado RM 26813 requiere una conciliación separada de inventario y sus referencias. La verificación de cero duplicados debe entenderse exclusivamente sobre la identidad de los encabezados POS, no como saneación de todas las series de la base.

## Respaldo y alcance

Respaldo privado: `/home/ing_adalberto/.local/state/appsiel/auditorias/karnikascdz-saneacion-2026-11-13/`, directorios 0700 y archivos 0600. Incluye diagnóstico inicial, snapshot, revisión de textos, evidencia adicional, prueba de propietarios, asignaciones por ID, scripts sin credenciales, servicio privado y hashes de su fuente original y adaptada.

El subdirectorio `ejecucion-20261114-034910-6af7da36a93ba/` contiene `plan.json`, `rollback-plan.json`, `manifest.json`, `validated.json`, `result.json` con `COMMITTED` y `verified.json` con cero duplicados POS. Los nombres internos usan el reloj PHP; la fecha del informe corresponde a America/Bogota.

Los planes inversos requieren cotejar el estado actual antes de restaurar, para no sobrescribir operaciones posteriores. No se ejecutaron migraciones, no se agregaron índices ni se alteraron documentos externos a esta base de datos.
