# Colegio Bilingüe: prestaciones sociales

Revisión del 10 de noviembre de 2026 sobre `u883985631_colegiobilingu`. Consultas directas PDO en transacciones `START TRANSACTION READ ONLY`, cerradas con `ROLLBACK`. Sin modificaciones de datos ni esquema.

Este documento describe el estado anterior a la regularización autorizada posteriormente. Los asientos de enero a mayo y sus verificaciones constan en [el informe de ejecución](colegio-bilingue-provisiones-enero-mayo-2026.md).

## Resultado

Sí se migraron liquidaciones de prestaciones y contabilidad de biaBLE. La ausencia de gastos en 2026 también existe en el respaldo fuente disponible; no se encontraron movimientos fuente de 2026 para las ocho cuentas de gasto/costo examinadas.

Fuente contrastada: `appsiel/database/init/biable01.sql`, tabla `CGMOVIMIENTO_CONTABLE`, con 10.243 filas de 2026 entre el 1 de enero y el 4 de septiembre. Lectura del archivo mediante CSV con comillas simples y escape de MySQL: ninguna fila contable quedó sin interpretar.

## Evidencia contable

En producción no hay movimientos de 2026 en `510530`, `510533`, `510536`, `510539`, `61600504`, `61600505`, `61600506` ni `61600507`. Tampoco los hay en esas cuentas en el movimiento contable de 2026 del respaldo biaBLE.

Los siguientes movimientos de 2026, identificados en producción con `creado_por = 'Migracion Biable Contabilidad 2026'`, coinciden con el respaldo en número de filas y valores:

| Cuenta | Prestación | Filas | Débitos COP | Créditos COP (magnitud) |
| --- | --- | ---: | ---: | ---: |
| 261005 | Cesantías | 88 | 162.818.861 | 0 |
| 261010 | Intereses de cesantías | 191 | 30.319.345 | 0 |
| 261015 | Prima de servicios | 114 | 216.982.889 | 214.148 |
| 261020 | Vacaciones | 89 | 124.260.553 | 0 |

Son movimientos de cuentas de pasivo; su existencia no supone reconocimiento del gasto del periodo.

## Nómina y provisiones

En nómina existen documentos con marca `Migracion` desde 2021 hasta junio de 2026. Para registros de 2026, la suma de devengos de conceptos cuyo nombre contiene cesantías, intereses, prima o vacaciones es:

| Familia de conceptos | Devengos COP |
| --- | ---: |
| Cesantías, incluida consignación | 370.534.559 |
| Intereses de cesantías | 29.505.348 |
| Prima | 215.107.522 |
| Vacaciones | 116.333.241 |

Estas sumas describen registros de nómina, no gastos contables ni una conciliación de pagos.

`nom_consolidados_prestaciones_sociales` contiene únicamente 432 filas con estado `Saldo inicial`, todas al 30 de mayo de 2026: 108 por prestación. No hay consolidados con estado `Activo`.

`nom_contabilizaciones_procesos` no contiene filas. Los cuatro parámetros del grupo de empleados 1 sí tienen cuentas configuradas: débito a las cuentas `51053x` indicadas y crédito a las correspondientes `2610xx`.

El código local `appsiel/app/Nomina/Services/ContabilizacionProvisionNomina.php` selecciona exclusivamente consolidados con estado `Activo` para el mes solicitado. Por ello, los saldos iniciales existentes no son entradas elegibles para ese proceso. El script de migración de nómina asigna los conceptos liquidados de prestaciones a cuentas `2610xx`; ese mapeo tampoco genera por sí mismo la provisión mensual de gasto.

La evidencia es compatible con provisiones mensuales pendientes de generación y contabilización. No permite determinar si existían en otro respaldo, archivo o sistema no examinado. Antes de registrar ajustes, corresponde conciliar los saldos iniciales, las liquidaciones y los consolidados mensuales por empleado y periodo.

## Hallazgo adicional y límites

Hay 10.228 filas contables de 2026 con la marca de migración en producción, frente a 10.243 en el respaldo. La comparación agrupada por fecha, tipo documental normalizado, consecutivo y cuenta ubica la diferencia en 15 filas del documento fuente NI/NP 1881 del 31 de julio de 2026. Las cuentas son `13060501`, `13060503`, `27064701`, `27064702`, `27064704`, `27064705`, `416001` y `416004`; no corresponden a prestaciones. No se determinó si se reemplazaron con otra marca o se retiraron posteriormente. Por tanto, esta revisión no certifica la integridad de toda la migración.

El corte fuente examinado es el 4 de septiembre de 2026. La afirmación sobre ausencia de gastos en biaBLE se limita a ese respaldo; la consulta de cuentas sin movimientos en producción cubre todo 2026.
