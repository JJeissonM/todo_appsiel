/* ==========================================================================
   Maestros para facturas de compra migradas desde Biable

   - Un grupo y un producto tipo servicio por cada cuenta de gasto/costo
     (clases 5 y 6) debitada en las causaciones CC de 2026.
   - Una clase de proveedor por cada cuenta por pagar 2335 acreditada en CC.

   Escritura permanente exclusiva:
     - inv_grupos
     - inv_productos
     - compras_clases_proveedores

   El proceso es idempotente: completa solamente las cuentas sin maestro.
   ========================================================================= */

USE `u883985631_colegiobilingu`;
SET NAMES utf8mb4;

SET @EMPRESA_ID := 1;
SET @MARCA := 'Migracion Biable Maestros Compras 2026';
SET @CUENTAS_SERVICIO_ESPERADAS := 30;
SET @CUENTAS_CXP_ESPERADAS := 11;
SET @IMPUESTO_NO_GRAVADO_ID := 1;
SET @CLASE_PROVEEDOR_PADRE_ID := 1;

DROP TEMPORARY TABLE IF EXISTS tmp_mig_cuentas_servicios;
CREATE TEMPORARY TABLE tmp_mig_cuentas_servicios (
  secuencia INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  contab_cuenta_id INT UNSIGNED NOT NULL,
  codigo VARCHAR(20) NOT NULL,
  descripcion VARCHAR(255) NOT NULL,
  UNIQUE KEY uq_tmp_mig_servicio_cuenta(contab_cuenta_id),
  UNIQUE KEY uq_tmp_mig_servicio_codigo(codigo)
);
INSERT INTO tmp_mig_cuentas_servicios(contab_cuenta_id,codigo,descripcion)
SELECT DISTINCT c.id,c.codigo,c.descripcion
FROM contab_movimientos m
JOIN contab_cuentas c ON c.id=m.contab_cuenta_id
WHERE m.core_empresa_id=@EMPRESA_ID
  AND m.creado_por='Migracion Biable Contabilidad 2026'
  AND m.core_tipo_transaccion_id=25
  AND m.core_tipo_doc_app_id=17
  AND m.valor_debito>0
  AND c.codigo REGEXP '^[56]'
ORDER BY c.codigo;

DROP TEMPORARY TABLE IF EXISTS tmp_mig_cuentas_cxp;
CREATE TEMPORARY TABLE tmp_mig_cuentas_cxp (
  secuencia INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  contab_cuenta_id INT UNSIGNED NOT NULL,
  codigo VARCHAR(20) NOT NULL,
  descripcion VARCHAR(255) NOT NULL,
  UNIQUE KEY uq_tmp_mig_cxp_cuenta(contab_cuenta_id),
  UNIQUE KEY uq_tmp_mig_cxp_codigo(codigo)
);
INSERT INTO tmp_mig_cuentas_cxp(contab_cuenta_id,codigo,descripcion)
SELECT DISTINCT c.id,c.codigo,c.descripcion
FROM contab_movimientos m
JOIN contab_cuentas c ON c.id=m.contab_cuenta_id
WHERE m.core_empresa_id=@EMPRESA_ID
  AND m.creado_por='Migracion Biable Contabilidad 2026'
  AND m.core_tipo_transaccion_id=25
  AND m.core_tipo_doc_app_id=17
  AND m.valor_credito<0
  AND c.codigo LIKE '2335%'
ORDER BY c.codigo;

/* Validaciones de fuente antes de escribir. */
SET @actual := (SELECT COUNT(*) FROM tmp_mig_cuentas_servicios);
SET @sql_validar := IF(@actual=@CUENTAS_SERVICIO_ESPERADAS,'DO 0',
  CONCAT("SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Cuentas de servicio inesperadas: ",@actual,"'"));
PREPARE validar FROM @sql_validar; EXECUTE validar; DEALLOCATE PREPARE validar;

SET @actual := (SELECT COUNT(*) FROM tmp_mig_cuentas_cxp);
SET @sql_validar := IF(@actual=@CUENTAS_CXP_ESPERADAS,'DO 0',
  CONCAT("SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Cuentas CxP inesperadas: ",@actual,"'"));
PREPARE validar FROM @sql_validar; EXECUTE validar; DEALLOCATE PREPARE validar;

SET @actual := (SELECT COUNT(*) FROM contab_impuestos
  WHERE id=@IMPUESTO_NO_GRAVADO_ID AND estado='Activo');
SET @sql_validar := IF(@actual=1
  AND EXISTS(SELECT 1 FROM compras_clases_proveedores
             WHERE id=@CLASE_PROVEEDOR_PADRE_ID),
  'DO 0',"SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Falta impuesto no gravado o clase padre'");
PREPARE validar FROM @sql_validar; EXECUTE validar; DEALLOCATE PREPARE validar;

SET @grupos_antes := (SELECT COUNT(DISTINCT s.contab_cuenta_id)
  FROM tmp_mig_cuentas_servicios s
  JOIN inv_grupos g ON g.core_empresa_id=@EMPRESA_ID
   AND g.cta_inventarios_id=s.contab_cuenta_id);
SET @productos_antes := (SELECT COUNT(DISTINCT s.contab_cuenta_id)
  FROM tmp_mig_cuentas_servicios s
  JOIN inv_productos p ON p.core_empresa_id=@EMPRESA_ID
   AND p.referencia=CONCAT('BIABLE-',s.codigo));
SET @clases_antes := (SELECT COUNT(DISTINCT c.contab_cuenta_id)
  FROM tmp_mig_cuentas_cxp c
  JOIN compras_clases_proveedores p ON p.cta_x_pagar_id=c.contab_cuenta_id);

SET @orden_base := (SELECT COALESCE(MAX(orden),0) FROM inv_grupos
  WHERE core_empresa_id=@EMPRESA_ID);

START TRANSACTION;

INSERT INTO inv_grupos
(core_empresa_id,descripcion,nivel_padre,tipo_nivel,imagen,orden,
 cta_inventarios_id,cta_ingresos_id,mostrar_en_pagina_web,estado,
 created_at,updated_at)
SELECT @EMPRESA_ID,LEFT(CONCAT('Biable ',s.codigo,' - ',s.descripcion),255),
       1,'grupo','',@orden_base+s.secuencia,s.contab_cuenta_id,0,0,'Activo',
       NOW(),NOW()
FROM tmp_mig_cuentas_servicios s
LEFT JOIN inv_grupos g ON g.core_empresa_id=@EMPRESA_ID
 AND g.cta_inventarios_id=s.contab_cuenta_id
WHERE g.id IS NULL;

INSERT INTO inv_productos
(core_empresa_id,tipo,descripcion,unidad_medida1,unidad_medida2,categoria_id,
 inv_grupo_id,impuesto_id,precio_compra,precio_venta,estado,referencia,
 codigo_barras,imagen,mostrar_en_pagina_web,prefijo_referencia_id,creado_por,
 modificado_por,created_at,updated_at,detalle)
SELECT @EMPRESA_ID,'servicio',s.descripcion,'94','UND','',MIN(g.id),
       @IMPUESTO_NO_GRAVADO_ID,0,0,'Activo',CONCAT('BIABLE-',s.codigo),
       NULL,'',0,NULL,@MARCA,@MARCA,NOW(),NOW(),
       CONCAT('Servicio Biable asociado a la cuenta contable ',s.codigo)
FROM tmp_mig_cuentas_servicios s
JOIN inv_grupos g ON g.core_empresa_id=@EMPRESA_ID
 AND g.cta_inventarios_id=s.contab_cuenta_id
LEFT JOIN inv_productos p ON p.core_empresa_id=@EMPRESA_ID
 AND p.referencia=CONCAT('BIABLE-',s.codigo)
WHERE p.id IS NULL
GROUP BY s.contab_cuenta_id,s.codigo,s.descripcion;

INSERT INTO compras_clases_proveedores
(descripcion,cta_x_pagar_id,cta_anticipo_id,clase_padre_id,estado,
 created_at,updated_at)
SELECT LEFT(CONCAT('Biable ',c.codigo,' - ',c.descripcion),255),
       c.contab_cuenta_id,0,@CLASE_PROVEEDOR_PADRE_ID,'Activo',NOW(),NOW()
FROM tmp_mig_cuentas_cxp c
LEFT JOIN compras_clases_proveedores p
  ON p.cta_x_pagar_id=c.contab_cuenta_id
WHERE p.id IS NULL;

/* Cada cuenta debe quedar cubierta exactamente por su maestro funcional. */
SET @actual := (SELECT COUNT(DISTINCT s.contab_cuenta_id)
  FROM tmp_mig_cuentas_servicios s
  JOIN inv_grupos g ON g.core_empresa_id=@EMPRESA_ID
   AND g.cta_inventarios_id=s.contab_cuenta_id);
SET @sql_validar := IF(@actual=@CUENTAS_SERVICIO_ESPERADAS,'DO 0',
  CONCAT("SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Grupos de servicio cubiertos: ",@actual,"'"));
PREPARE validar FROM @sql_validar; EXECUTE validar; DEALLOCATE PREPARE validar;

SET @actual := (SELECT COUNT(DISTINCT s.contab_cuenta_id)
  FROM tmp_mig_cuentas_servicios s
  JOIN inv_productos p ON p.core_empresa_id=@EMPRESA_ID
   AND p.tipo='servicio' AND p.referencia=CONCAT('BIABLE-',s.codigo)
  JOIN inv_grupos g ON g.id=p.inv_grupo_id
   AND g.cta_inventarios_id=s.contab_cuenta_id);
SET @sql_validar := IF(@actual=@CUENTAS_SERVICIO_ESPERADAS,'DO 0',
  CONCAT("SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Productos de servicio cubiertos: ",@actual,"'"));
PREPARE validar FROM @sql_validar; EXECUTE validar; DEALLOCATE PREPARE validar;

SET @actual := (SELECT COUNT(DISTINCT c.contab_cuenta_id)
  FROM tmp_mig_cuentas_cxp c
  JOIN compras_clases_proveedores p ON p.cta_x_pagar_id=c.contab_cuenta_id);
SET @sql_validar := IF(@actual=@CUENTAS_CXP_ESPERADAS,'DO 0',
  CONCAT("SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Clases de proveedor cubiertas: ",@actual,"'"));
PREPARE validar FROM @sql_validar; EXECUTE validar; DEALLOCATE PREPARE validar;

COMMIT;

SELECT @CUENTAS_SERVICIO_ESPERADAS-@grupos_antes grupos_creados,
       @CUENTAS_SERVICIO_ESPERADAS-@productos_antes servicios_creados,
       @CUENTAS_CXP_ESPERADAS-@clases_antes clases_proveedor_creadas;

SELECT s.codigo,s.descripcion,MIN(g.id) inv_grupo_id,MIN(p.id) inv_producto_id,
       MIN(p.referencia) referencia
FROM tmp_mig_cuentas_servicios s
JOIN inv_grupos g ON g.core_empresa_id=@EMPRESA_ID
 AND g.cta_inventarios_id=s.contab_cuenta_id
JOIN inv_productos p ON p.core_empresa_id=@EMPRESA_ID
 AND p.inv_grupo_id=g.id AND p.referencia=CONCAT('BIABLE-',s.codigo)
GROUP BY s.codigo,s.descripcion
ORDER BY s.codigo;

SELECT c.codigo,c.descripcion,MIN(p.id) clase_proveedor_id
FROM tmp_mig_cuentas_cxp c
JOIN compras_clases_proveedores p ON p.cta_x_pagar_id=c.contab_cuenta_id
GROUP BY c.codigo,c.descripcion
ORDER BY c.codigo;
