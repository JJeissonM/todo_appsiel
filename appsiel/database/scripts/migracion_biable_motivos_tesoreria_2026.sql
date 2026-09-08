/* ==========================================================================
   Motivos de tesoreria para Pagos/Recaudos generales

   - Gastos/costos debitados en comprobantes de egreso Biable (CE):
     motivo de salida para Pagos de tesoreria.
   - Ingresos acreditados en recibos de caja Biable (RC):
     motivo de entrada para Recaudos.

   Escritura permanente exclusiva: teso_motivos.
   El proceso es idempotente: completa solamente las cuentas sin motivo.
   ========================================================================= */

USE `u883985631_colegiobilingu`;
SET NAMES utf8mb4;

SET @EMPRESA_ID := 1;
SET @CUENTAS_PAGOS_ESPERADAS := 18;
SET @CUENTAS_RECAUDOS_ESPERADAS := 10;

DROP TEMPORARY TABLE IF EXISTS tmp_mig_motivos_pagos;
CREATE TEMPORARY TABLE tmp_mig_motivos_pagos AS
SELECT DISTINCT c.id AS contab_cuenta_id,c.codigo,c.descripcion
FROM contab_movimientos m
JOIN contab_cuentas c ON c.id=m.contab_cuenta_id
WHERE m.core_empresa_id=@EMPRESA_ID
  AND m.creado_por='Migracion Biable Contabilidad 2026'
  AND m.core_tipo_transaccion_id=57 /* CE: Comprobantes de egreso */
  AND m.valor_debito>0
  AND c.codigo REGEXP '^[56]';
ALTER TABLE tmp_mig_motivos_pagos
  ADD PRIMARY KEY(contab_cuenta_id),ADD UNIQUE KEY uq_tmp_pago_codigo(codigo);

DROP TEMPORARY TABLE IF EXISTS tmp_mig_motivos_recaudos;
CREATE TEMPORARY TABLE tmp_mig_motivos_recaudos AS
SELECT DISTINCT c.id AS contab_cuenta_id,c.codigo,c.descripcion
FROM contab_movimientos m
JOIN contab_cuentas c ON c.id=m.contab_cuenta_id
WHERE m.core_empresa_id=@EMPRESA_ID
  AND m.creado_por='Migracion Biable Contabilidad 2026'
  AND m.core_tipo_transaccion_id=56 /* RC: Recibos de caja */
  AND m.valor_credito<0
  AND c.codigo LIKE '4%';
ALTER TABLE tmp_mig_motivos_recaudos
  ADD PRIMARY KEY(contab_cuenta_id),ADD UNIQUE KEY uq_tmp_recaudo_codigo(codigo);

/* Validaciones de la fuente y de los tipos funcionales de Appsiel. */
SET @actual := (SELECT COUNT(*) FROM tmp_mig_motivos_pagos);
SET @sql_validar := IF(@actual=@CUENTAS_PAGOS_ESPERADAS,'DO 0',
  CONCAT("SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Cuentas de pagos inesperadas: ",@actual,"'"));
PREPARE validar FROM @sql_validar; EXECUTE validar; DEALLOCATE PREPARE validar;

SET @actual := (SELECT COUNT(*) FROM tmp_mig_motivos_recaudos);
SET @sql_validar := IF(@actual=@CUENTAS_RECAUDOS_ESPERADAS,'DO 0',
  CONCAT("SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Cuentas de recaudos inesperadas: ",@actual,"'"));
PREPARE validar FROM @sql_validar; EXECUTE validar; DEALLOCATE PREPARE validar;

SET @actual := (SELECT COUNT(*) FROM sys_tipos_transacciones
  WHERE id IN (8,17) AND estado='Activo');
SET @sql_validar := IF(@actual=2,'DO 0',
  "SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Falta transaccion Recaudo o Pagos de tesoreria'");
PREPARE validar FROM @sql_validar; EXECUTE validar; DEALLOCATE PREPARE validar;

SET @pagos_antes := (SELECT COUNT(DISTINCT c.contab_cuenta_id)
  FROM tmp_mig_motivos_pagos c
  JOIN teso_motivos m ON m.core_empresa_id=@EMPRESA_ID
   AND m.core_tipo_transaccion_id=17 AND m.movimiento='salida'
   AND m.contab_cuenta_id=c.contab_cuenta_id);
SET @recaudos_antes := (SELECT COUNT(DISTINCT c.contab_cuenta_id)
  FROM tmp_mig_motivos_recaudos c
  JOIN teso_motivos m ON m.core_empresa_id=@EMPRESA_ID
   AND m.core_tipo_transaccion_id=8 AND m.movimiento='entrada'
   AND m.contab_cuenta_id=c.contab_cuenta_id);

START TRANSACTION;

INSERT INTO teso_motivos
(core_empresa_id,core_tipo_transaccion_id,descripcion,movimiento,estado,
 teso_tipo_motivo,contab_cuenta_id,created_at,updated_at)
SELECT @EMPRESA_ID,17,LEFT(CONCAT('Biable ',c.codigo,' - ',c.descripcion),255),
       'salida','Activo','otros-pagos',c.contab_cuenta_id,NOW(),NOW()
FROM tmp_mig_motivos_pagos c
LEFT JOIN teso_motivos m ON m.core_empresa_id=@EMPRESA_ID
 AND m.core_tipo_transaccion_id=17 AND m.movimiento='salida'
 AND m.contab_cuenta_id=c.contab_cuenta_id
WHERE m.id IS NULL;

INSERT INTO teso_motivos
(core_empresa_id,core_tipo_transaccion_id,descripcion,movimiento,estado,
 teso_tipo_motivo,contab_cuenta_id,created_at,updated_at)
SELECT @EMPRESA_ID,8,LEFT(CONCAT('Biable ',c.codigo,' - ',c.descripcion),255),
       'entrada','Activo','otros-recaudos',c.contab_cuenta_id,NOW(),NOW()
FROM tmp_mig_motivos_recaudos c
LEFT JOIN teso_motivos m ON m.core_empresa_id=@EMPRESA_ID
 AND m.core_tipo_transaccion_id=8 AND m.movimiento='entrada'
 AND m.contab_cuenta_id=c.contab_cuenta_id
WHERE m.id IS NULL;

/* Una cobertura funcional por cuenta; no se exigen IDs consecutivos. */
SET @actual := (SELECT COUNT(DISTINCT c.contab_cuenta_id)
  FROM tmp_mig_motivos_pagos c
  JOIN teso_motivos m ON m.core_empresa_id=@EMPRESA_ID
   AND m.core_tipo_transaccion_id=17 AND m.movimiento='salida'
   AND m.teso_tipo_motivo='otros-pagos'
   AND m.contab_cuenta_id=c.contab_cuenta_id);
SET @sql_validar := IF(@actual=@CUENTAS_PAGOS_ESPERADAS,'DO 0',
  CONCAT("SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Motivos de pagos cubiertos: ",@actual,"'"));
PREPARE validar FROM @sql_validar; EXECUTE validar; DEALLOCATE PREPARE validar;

SET @actual := (SELECT COUNT(DISTINCT c.contab_cuenta_id)
  FROM tmp_mig_motivos_recaudos c
  JOIN teso_motivos m ON m.core_empresa_id=@EMPRESA_ID
   AND m.core_tipo_transaccion_id=8 AND m.movimiento='entrada'
   AND m.teso_tipo_motivo='otros-recaudos'
   AND m.contab_cuenta_id=c.contab_cuenta_id);
SET @sql_validar := IF(@actual=@CUENTAS_RECAUDOS_ESPERADAS,'DO 0',
  CONCAT("SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Motivos de recaudos cubiertos: ",@actual,"'"));
PREPARE validar FROM @sql_validar; EXECUTE validar; DEALLOCATE PREPARE validar;

COMMIT;

SELECT @CUENTAS_PAGOS_ESPERADAS-@pagos_antes motivos_pagos_creados,
       @CUENTAS_RECAUDOS_ESPERADAS-@recaudos_antes motivos_recaudos_creados;

SELECT 'Pago general' uso,c.codigo,c.descripcion,MIN(m.id) teso_motivo_id
FROM tmp_mig_motivos_pagos c
JOIN teso_motivos m ON m.core_empresa_id=@EMPRESA_ID
 AND m.core_tipo_transaccion_id=17 AND m.movimiento='salida'
 AND m.contab_cuenta_id=c.contab_cuenta_id
GROUP BY c.codigo,c.descripcion
UNION ALL
SELECT 'Recaudo general',c.codigo,c.descripcion,MIN(m.id)
FROM tmp_mig_motivos_recaudos c
JOIN teso_motivos m ON m.core_empresa_id=@EMPRESA_ID
 AND m.core_tipo_transaccion_id=8 AND m.movimiento='entrada'
 AND m.contab_cuenta_id=c.contab_cuenta_id
GROUP BY c.codigo,c.descripcion
ORDER BY uso,codigo;
