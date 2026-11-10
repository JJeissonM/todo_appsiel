# Provisiones de prestaciones: enero a mayo de 2026

Ejecutado el 10 de noviembre de 2026 en `u883985631_colegiobilingu`. Estado: **COMMITTED**, confirmado mediante una nueva conexión de lectura.

## Alcance y criterio autorizado

El usuario solicitó generar gasto y pasivo de vacaciones, prima, cesantías e intereses de enero a mayo de 2026, conservando los consolidados existentes de mayo como punto de partida para los procesos del sistema desde junio. Ante la diferencia entre el respaldo SQL de biaBLE y el archivo que originó los consolidados de mayo, confirmó: «Aplicar el criterio de mayo a la nómina histórica y conservar mayo».

Se generaron cinco notas internas y cinco registros en `nom_contabilizaciones_procesos`, con proceso `provision_prestaciones_sociales`. Se insertaron 4.336 movimientos contables y sus 4.336 líneas de documento. Cada prestación tiene débito al gasto y crédito al pasivo, por empleado, contrato y mes.

No se modificaron registros ni documentos de nómina, contratos, parámetros, consolidados de mayo o asientos anteriores. No se crearon consolidados de enero a abril, cuentas por pagar operativas ni movimientos de junio en adelante.

## Documentos guardados

Los importes están en pesos colombianos. Débitos y créditos tienen la misma magnitud en cada documento; Appsiel almacena los créditos del movimiento contable con signo negativo.

| Mes | Fecha | Documento | ID encabezado | Movimientos | Débitos = créditos |
| --- | --- | --- | ---: | ---: | ---: |
| Enero | 2026-01-31 | NI 1915 | 1735 | 864 | 89.338.708 |
| Febrero | 2026-02-28 | NI 1916 | 1736 | 864 | 90.463.238 |
| Marzo | 2026-03-31 | NI 1917 | 1737 | 880 | 91.424.976 |
| Abril | 2026-04-30 | NI 1918 | 1738 | 864 | 90.369.122 |
| Mayo | 2026-05-30 | NI 1919 | 1739 | 864 | 94.282.007 |
| **Total** | | | | **4.336** | **455.878.051** |

| Prestación | Gasto | Pasivo | Provisión enero–mayo |
| --- | --- | --- | ---: |
| Cesantías | 510530 | 261005 | 181.067.884 |
| Intereses de cesantías | 510533 | 261010 | 9.429.320 |
| Prima de servicios | 510536 | 261015 | 181.067.884 |
| Vacaciones | 510539 | 261020 | 84.312.963 |

Las cuentas son las configuradas en `nom_parametros_liquidacion_prestaciones_sociales` para el grupo 1, al cual pertenecen los contratos actuales. La clasificación histórica de docentes se utilizó para los factores de cálculo; las cuentas se tomaron de los parámetros actuales del colegio.

## Cálculo y trazabilidad

Se respaldaron 3.245 registros y 12 documentos de nómina de enero a mayo, 432 consolidados de mayo, parámetros, conceptos, agrupaciones, contratos y movimientos previos de las cuentas examinadas. Se vincularon los contratos con los maestros históricos de biaBLE mediante identificación y fecha de ingreso. Para tres contratos cuya fecha actual fue cambiada a agosto de 2026 se usó la fecha histórica del archivo de mayo para resolver la correspondencia, sin actualizar contratos.

Para enero a abril:

- La base salarial usa el sueldo y sus sustitutos efectivamente registrados: incapacidades, maternidad y licencias remuneradas. Incluye vacaciones disfrutadas; excluye vacaciones compensadas y liquidaciones de vacaciones por terminación de contrato.
- Para cesantías y prima se añaden los conceptos de la agrupación 19 configurada: transporte, extras y recargos. Las prestaciones ya liquidadas, consignaciones de cesantías, intereses pagados de años anteriores y reembolsos no se convierten en base salarial.
- En 22 registros de vacaciones disfrutadas con valor cero se reconstruyó la base usando la tarifa del sueldo del mismo empleado y documento: valor del sueldo dividido por sus horas, multiplicado por las horas de vacaciones. Así se conserva ese tiempo salarial sin usar salarios actuales.
- Se aplicó el factor de mayo: para docentes del grupo histórico biaBLE 02, cesantías y prima equivalen a base/10 y vacaciones a base salarial/20. Para los demás empleados, base/12 y base salarial/24, respectivamente.
- Los intereses mensuales son el incremento del acumulado de cesantías de 2026 × 12% × días transcurridos del año comercial/360. Se consideran ingreso y retiro históricos. Se redondea por contrato y prestación al peso. Los intereses pagados en enero por el año anterior no se cargan como gasto nuevo de 2026.

Para mayo se tomó exactamente `valor_consolidado_mes` de cada uno de los 432 registros existentes. No se recalcularon valores ni se cambiaron sus estados `Saldo inicial`.

Ejemplo comprobable: contrato 18554, sueldo histórico 2.600.000 y transporte 249.095. La provisión mensual de cesantías y prima de enero a abril es 284.910 cada una, y vacaciones 130.000. Estos mismos importes constan en mayo. Los intereses calculados de enero a abril son 2.849, 8.547, 14.246 y 19.944; el valor existente de mayo, conservado, es 25.641.

| Mes | Cesantías | Prima | Vacaciones | Intereses | Total |
| --- | ---: | ---: | ---: | ---: | ---: |
| Enero | 36.073.675 | 36.073.675 | 16.831.828 | 359.530 | 89.338.708 |
| Febrero | 36.227.295 | 36.227.295 | 16.935.451 | 1.073.197 | 90.463.238 |
| Marzo | 36.368.317 | 36.368.317 | 16.914.344 | 1.773.998 | 91.424.976 |
| Abril | 35.665.308 | 35.665.308 | 16.581.223 | 2.457.283 | 90.369.122 |
| Mayo | 36.733.289 | 36.733.289 | 17.050.117 | 3.765.312 | 94.282.007 |

## Validaciones realizadas

Se ejecutó primero la carga completa dentro de una transacción que finalizó con `ROLLBACK`. Después se repitió con `COMMIT`. Las tablas participantes son InnoDB y el contador NI se reservó bajo bloqueo; quedó en 1919. La ejecución rechaza una repetición por marca y también verifica que no existan provisiones previas del periodo.

La verificación posterior, desde otra conexión y en transacción de solo lectura, contrastó cada movimiento por fecha, cuenta, tercero, contrato, prestación e importe contra el soporte. Confirmó los cinco documentos cuadrados, las 4.336 referencias a líneas de documento y los cinco procesos. También comprobó que los registros de nómina, los 432 consolidados de mayo y los movimientos previos respaldados permanecen iguales.

Marca de identificación: `Regularizacion provisiones nomina enero-mayo 2026`.

## Conciliación pendiente del acumulado

La carga registra las causaciones solicitadas. **El saldo contable acumulado resultante todavía no coincide con el acumulado de los consolidados de mayo.** No se insertaron ajustes adicionales para forzar esa igualdad. Estas diferencias deben conciliarse antes de dar por conciliado el pasivo de apertura de junio.

| Prestación | Pasivo contable al cierre de mayo después de esta carga | Acumulado del consolidado de mayo | Diferencia contabilidad menos consolidado |
| --- | ---: | ---: | ---: |
| Cesantías | 178.071.169 | 234.187.502 | -56.116.333 |
| Intereses | 9.355.322 | 17.905.734 | -8.550.412 |
| Prima | 177.375.016 | 181.282.835 | -3.907.819 |
| Vacaciones | 107.539.351 | 227.948.147 | -120.408.796 |

Son diferencias de conciliación residual, no importes adicionales de gasto autorizados. Su causa no se determinó en esta carga: requieren revisar saldos iniciales, liquidaciones y diferencias de bases frente al archivo de mayo. El consolidado de mayo contiene además algunos acumulados negativos de vacaciones y un acumulado negativo de intereses; se conservaron como fueron recibidos.

## Soportes privados

Directorio persistente: `/home/ing_adalberto/.local/state/appsiel/colegio-provisiones-2026-01-05/`.

- `soporte_contabilizado.csv`: 2.168 filas de prestaciones, identificadas por empleado, contrato, mes, cuentas y NI guardada.
- `fuente.json`, `contexto.json`, `mapa_contratos.json`, `biable_maestros.json`, `biable_consolidados.json`, `excel_rows.json`: snapshots y referencias usadas en la preparación.
- `plan.json`, `preimagen_ejecucion.json`, `resultado_ejecucion.json`, `verificacion_posterior.json` y `manifest_sha256.json`: cálculo, contador anterior, constancia de COMMIT, validación y huellas de los soportes.

SHA-256 del plan contabilizado: `d9f6c7f574f643eb8888430a0c93c2fecfa39c44143ee50b63ca3d8ffd09c7c2`.

Scripts locales: `appsiel/database/scripts/preparar_provisiones_colegio_enero_mayo_2026.py` y `appsiel/database/scripts/contabilizar_provisiones_colegio_enero_mayo_2026.php`. Los archivos privados contienen información de nómina y no se agregaron al repositorio.
