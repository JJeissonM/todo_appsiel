# Distrikarnika2: conciliación de consecutivos POS en producción

Ejecución autorizada por el usuario el 13-11-2026 (America/Bogota) en `u883985631_distrikarnika2`. Estado: **COMMITTED**, verificado después del commit y desde una conexión independiente.

Se resolvieron los tres grupos duplicados del histórico de `vtas_pos_doc_encabezados` por empresa/transacción/tipo/consecutivo. Todas las facturas corresponden a empresa 1, transacción 47 y tipo 18, y estaban contabilizadas. En esta base el prefijo configurado del tipo 18 es `FV`; son facturas del módulo POS.

| ID renumerado | ID que conserva número | Anterior | Nuevo | Total de líneas |
| --- | --- | --- | --- | --- |
| 3081 | 3080 | 2833 | 2834 | $27.300 |
| 19403 | 19402 | 19034 | 19035 | $40.275 |
| 24625 | 24624 | 24256 | 24257 | $99.837,50 |

Se utilizaron los números siguientes libres de la misma serie. No se eliminaron ni fusionaron facturas. Se conservaron importes, ajustes al peso, fechas, estados, IDs, relaciones por ID, inventario y contador.

## Cambios confirmados

| Tabla | Campo | Filas |
| --- | --- | --- |
| `vtas_pos_doc_encabezados` | `consecutivo` | 3 |
| `vtas_pos_movimientos` | `consecutivo` | 12 |
| `vtas_movimientos` | `consecutivo` | 12 |
| `teso_movimientos` | `consecutivo` | 2 |
| `contab_movimientos` | `consecutivo` | 17 |
| `cxc_movimientos` | `consecutivo` | 1 |
| `cxc_abonos` | `doc_cxc_consecutivo` | 1 |

Total: **48 filas**, en una sola transacción InnoDB. Solo cambiaron los campos de numeración y la referencia de cartera indicados.

La factura 24625 pasa a 24257 junto con su cuenta por cobrar ID 2263 y la referencia de su abono ID 2652. El total de líneas es $99.837,50; la cartera y su abono conservan $99.800, incluido el ajuste al peso original. El documento de cartera continúa pagado y con saldo cero. El abono conserva su consecutivo propio 2316.

## Referencias de otra serie revisadas

El primer diagnóstico del comando `pos:conciliar-consecutivos` abortó sin escribir datos al encontrar los números 2833 y 2834 en `contab_movimientos.detalle_operacion`. Se revisaron las dos filas y se confirmó que son referencias a recibos de caja de otra identidad:

- ID 277117: empresa 1, transacción 32, tipo 3 (`RC`), consecutivo 2833; texto `Contabilización Recaudos de CxC RC 2833`.
- ID 277119: empresa 1, transacción 32, tipo 3 (`RC`), consecutivo 2834; texto `Contabilización Recaudos de CxC RC 2834`.

La revisión completa de campos textuales del esquema encontró únicamente estas dos coincidencias. Se conservaron ambas filas completas.

Para completar esta ejecución se invocó la misma clase de comando mediante un lanzador privado, con la base y el usuario restringidos explícitamente a `u883985631_distrikarnika2`. Se usó una copia privada del servicio con una excepción limitada a esos dos IDs y al campo `detalle_operacion`: exige coincidencia de cada fila completa con el respaldo revisado y revalida el prefijo `RC` del tipo 3. La excepción solo evita interpretar esos textos como referencias POS; las consultas y las filas RC permanecen en el plan, el respaldo y las comparaciones anteriores y posteriores a los cambios.

No se modificó el comando ni el servicio del repositorio para esta ejecución. Los archivos de la revisión privada, su justificación y los hashes del servicio original y del revisado están en el respaldo.

## Controles y evidencia

- Diagnóstico inicial y revisión de referencias en transacciones de solo lectura.
- Validación de propietario de cada movimiento mediante identidad documental, usuario, fecha, tercero, PDV/cajero y remisión donde corresponde. Cotejo de productos, cantidades e importes con los detalles por ID; equilibrio contable, recaudos, cartera y abonos con ajustes al peso.
- Verificación de destinos libres y contador existente suficiente, tablas de negocio InnoDB y ausencia de triggers.
- Plan completo y plan inverso guardados antes de escribir; manifiesto SHA-256 de ambos. Directorios privados y archivos restringidos.
- Bloqueos del contador y filas, revalidación de esquema y datos frente al respaldo, UPDATE por IDs explícitos y conteos exactos.
- Comparación de todas las columnas de las filas consultadas con el resultado previsto, y comprobación de **cero grupos duplicados en todo el histórico POS** antes de confirmar y desde una transacción nueva después del commit.
- El plan contiene 137 comprobaciones de consultas, incluidas las referencias RC que deben conservarse. Una conexión independiente repitió las 137 comprobaciones, comparó todas las filas con el resultado esperado, confirmó los dos RC intactos y verificó cero grupos duplicados. Evidencia: `independent-verification.json`.

## Respaldo y alcance

Respaldo privado: `/home/ing_adalberto/.local/state/appsiel/auditorias/distrikarnika2-saneacion-2026-11-13/`, directorios 0700 y archivos 0600. Los nombres internos de ejecución llevan la fecha del reloj PHP; la fecha del informe corresponde al contexto del usuario en America/Bogota.

El subdirectorio `ejecucion-20261114-032400-6af7d45046e78/` contiene `plan.json`, `rollback-plan.json`, `manifest.json`, `validated.json`, `result.json` con `COMMITTED` y `verified.json` con cero grupos duplicados. La raíz conserva los diagnósticos, revisión completa de textos, filas RC revisadas, scripts sin credenciales y manifiesto SHA-256 general.

Los planes inversos requieren revalidar el estado actual antes de restaurar; no deben aplicarse sobre operaciones posteriores sin cotejo. No se ejecutaron migraciones, no se agregaron índices y no se alteraron documentos externos a esta base.
