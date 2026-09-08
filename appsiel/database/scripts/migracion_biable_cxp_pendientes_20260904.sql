/* ==========================================================================
   Saldos pendientes de cuentas por pagar Biable -> Appsiel
   Corte fuente: 4 de septiembre de 2026 (lapso 202609)

   Escritura permanente exclusiva:
     - cxp_movimientos

   Requiere el snapshot mig_biable_cxp_pendientes_20260904 en el destino.
   Es idempotente: reemplaza solo las filas identificadas con @MARCA.
   ========================================================================= */

USE `u883985631_colegiobilingu`;
SET NAMES utf8mb4;

SET @EMPRESA_ID := 1;
SET @MARCA := 'Migracion Biable CxP 20260904';
SET @LINEAS_ESPERADAS := 47;
SET @TERCEROS_ESPERADOS := 38;
SET @VALOR_ORIGINAL_ESPERADO := 421803422.00;
SET @VALOR_PAGADO_ESPERADO := 203636000.00;
SET @SALDO_ESPERADO := 218167422.00;

DROP TEMPORARY TABLE IF EXISTS tmp_mig_biable_cxp_terceros;
CREATE TEMPORARY TABLE tmp_mig_biable_cxp_terceros AS
SELECT s.numero_identificacion,MIN(t.id) AS core_tercero_id
FROM (SELECT DISTINCT numero_identificacion
      FROM mig_biable_cxp_pendientes_20260904) s
LEFT JOIN core_terceros t
  ON t.core_empresa_id=@EMPRESA_ID
 AND t.numero_identificacion=s.numero_identificacion
GROUP BY s.numero_identificacion;
ALTER TABLE tmp_mig_biable_cxp_terceros
  ADD PRIMARY KEY (numero_identificacion),
  ADD INDEX ix_tmp_mig_biable_cxp_tercero(core_tercero_id);

/* Validaciones bloqueantes de la fuente antes de escribir. */
SET @actual := (SELECT COUNT(*) FROM mig_biable_cxp_pendientes_20260904);
SET @sql_validar := IF(@actual=@LINEAS_ESPERADAS,'DO 0',
  CONCAT("SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Lineas CxP inesperadas: ",@actual,"'"));
PREPARE validar FROM @sql_validar; EXECUTE validar; DEALLOCATE PREPARE validar;

SET @actual := (SELECT COUNT(DISTINCT numero_identificacion)
  FROM mig_biable_cxp_pendientes_20260904);
SET @sql_validar := IF(@actual=@TERCEROS_ESPERADOS,'DO 0',
  CONCAT("SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Terceros CxP inesperados: ",@actual,"'"));
PREPARE validar FROM @sql_validar; EXECUTE validar; DEALLOCATE PREPARE validar;

SET @actual := (SELECT COUNT(*) FROM mig_biable_cxp_pendientes_20260904
  WHERE numero_identificacion IS NULL OR tipo_cruce<>'CC'
     OR saldo_pendiente<=0 OR valor_original<saldo_pendiente
     OR fecha_documento NOT REGEXP '^[0-9]{8}$'
     OR fecha_vencimiento NOT REGEXP '^[0-9]{8}$');
SET @sql_validar := IF(@actual=0,'DO 0',
  CONCAT("SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Filas CxP invalidas: ",@actual,"'"));
PREPARE validar FROM @sql_validar; EXECUTE validar; DEALLOCATE PREPARE validar;

SET @actual := (SELECT COUNT(*) FROM tmp_mig_biable_cxp_terceros
  WHERE core_tercero_id IS NULL);
SET @sql_validar := IF(@actual=0,'DO 0',
  CONCAT("SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Terceros CxP no encontrados: ",@actual,"'"));
PREPARE validar FROM @sql_validar; EXECUTE validar; DEALLOCATE PREPARE validar;

SET @valor_original := (SELECT ROUND(SUM(valor_original),2)
  FROM mig_biable_cxp_pendientes_20260904);
SET @valor_pagado := (SELECT ROUND(SUM(valor_pagado),2)
  FROM mig_biable_cxp_pendientes_20260904);
SET @saldo := (SELECT ROUND(SUM(saldo_pendiente),2)
  FROM mig_biable_cxp_pendientes_20260904);
SET @sql_validar := IF(
  ABS(@valor_original-@VALOR_ORIGINAL_ESPERADO)<=0.05
  AND ABS(@valor_pagado-@VALOR_PAGADO_ESPERADO)<=0.05
  AND ABS(@saldo-@SALDO_ESPERADO)<=0.05,
  'DO 0',"SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Totales CxP fuente no coinciden'");
PREPARE validar FROM @sql_validar; EXECUTE validar; DEALLOCATE PREPARE validar;

START TRANSACTION;

DELETE FROM cxp_movimientos
WHERE core_empresa_id=@EMPRESA_ID AND creado_por=@MARCA;

/* CC Biable corresponde a Factura de compras FC en Appsiel. */
INSERT INTO cxp_movimientos
(core_tipo_transaccion_id,core_tipo_doc_app_id,consecutivo,core_empresa_id,
 core_tercero_id,modelo_referencia_tercero_index,referencia_tercero_id,
 doc_proveedor_prefijo,doc_proveedor_consecutivo,fecha,fecha_vencimiento,
 valor_documento,valor_pagado,saldo_pendiente,estado,detalle,creado_por,
 modificado_por,created_at,updated_at)
SELECT
  25,17,CAST(s.numero_cruce AS UNSIGNED),@EMPRESA_ID,t.core_tercero_id,'',0,
  s.prefijo_proveedor,s.numero_proveedor,
  STR_TO_DATE(s.fecha_documento,'%Y%m%d'),
  STR_TO_DATE(s.fecha_vencimiento,'%Y%m%d'),
  s.valor_original,s.valor_pagado,s.saldo_pendiente,'Pendiente',
  CONCAT('Saldo CxP Biable; cuenta ',s.cuenta,'; documento ',
         s.tipo_cruce,' ',s.numero_cruce),
  @MARCA,@MARCA,NOW(),NOW()
FROM mig_biable_cxp_pendientes_20260904 s
JOIN tmp_mig_biable_cxp_terceros t
  ON t.numero_identificacion=s.numero_identificacion;

/* Validaciones bloqueantes de lo insertado. */
SET @actual := (SELECT COUNT(*) FROM cxp_movimientos
  WHERE core_empresa_id=@EMPRESA_ID AND creado_por=@MARCA);
SET @sql_validar := IF(@actual=@LINEAS_ESPERADAS,'DO 0',
  CONCAT("SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Lineas CxP cargadas: ",@actual,"'"));
PREPARE validar FROM @sql_validar; EXECUTE validar; DEALLOCATE PREPARE validar;

SET @valor_original := (SELECT ROUND(SUM(valor_documento),2) FROM cxp_movimientos
  WHERE core_empresa_id=@EMPRESA_ID AND creado_por=@MARCA);
SET @valor_pagado := (SELECT ROUND(SUM(valor_pagado),2) FROM cxp_movimientos
  WHERE core_empresa_id=@EMPRESA_ID AND creado_por=@MARCA);
SET @saldo := (SELECT ROUND(SUM(saldo_pendiente),2) FROM cxp_movimientos
  WHERE core_empresa_id=@EMPRESA_ID AND creado_por=@MARCA);
SET @actual := (SELECT COUNT(*) FROM cxp_movimientos
  WHERE core_empresa_id=@EMPRESA_ID AND creado_por=@MARCA
    AND ABS(valor_documento-valor_pagado-saldo_pendiente)>0.01);
SET @sql_validar := IF(
  @actual=0
  AND ABS(@valor_original-@VALOR_ORIGINAL_ESPERADO)<=0.05
  AND ABS(@valor_pagado-@VALOR_PAGADO_ESPERADO)<=0.05
  AND ABS(@saldo-@SALDO_ESPERADO)<=0.05,
  'DO 0',"SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Validacion final CxP no coincide'");
PREPARE validar FROM @sql_validar; EXECUTE validar; DEALLOCATE PREPARE validar;

COMMIT;

SELECT 'TOTAL' cuenta,COUNT(*) documentos,COUNT(DISTINCT core_tercero_id) terceros,
       SUM(valor_documento) valor_original,SUM(valor_pagado) valor_pagado,
       SUM(saldo_pendiente) saldo_pendiente
FROM cxp_movimientos
WHERE core_empresa_id=@EMPRESA_ID AND creado_por=@MARCA;

SELECT cuenta,COUNT(*) documentos,COUNT(DISTINCT numero_identificacion) terceros,
       SUM(valor_original) valor_original,SUM(valor_pagado) valor_pagado,
       SUM(saldo_pendiente) saldo_pendiente
FROM mig_biable_cxp_pendientes_20260904
GROUP BY cuenta
ORDER BY cuenta;
