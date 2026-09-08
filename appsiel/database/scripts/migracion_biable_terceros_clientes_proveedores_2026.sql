/* ==========================================================================
   Especializacion de terceros como clientes y proveedores

   Crea el registro faltante en:
     - compras_proveedores
     - vtas_clientes

   No modifica especializaciones existentes. Para terceros con saldos CxP
   migrados asigna la clase de proveedor de su cuenta 2335; para los demas
   utiliza la clase general ID 1. El proceso es idempotente por tercero.
   ========================================================================= */

USE `u883985631_colegiobilingu`;
SET NAMES utf8mb4;

SET @EMPRESA_ID := 1;
SET @CLASE_PROVEEDOR_GENERAL_ID := 1;
SET @BODEGA_ID := 1;
SET @CONDICION_PAGO_COMPRAS_ID := 1;
SET @CLASE_CLIENTE_ID := 1;
SET @LISTA_PRECIOS_ID := 1;
SET @LISTA_DESCUENTOS_ID := 1;
SET @VENDEDOR_ID := 1;
SET @ZONA_ID := 1;
SET @CONDICION_PAGO_VENTAS_ID := 1;

DROP TEMPORARY TABLE IF EXISTS tmp_mig_clase_proveedor_tercero;
CREATE TEMPORARY TABLE tmp_mig_clase_proveedor_tercero AS
SELECT m.core_tercero_id,MIN(cp.id) AS clase_proveedor_id
FROM cxp_movimientos m
JOIN contab_cuentas c
  ON c.core_empresa_id=@EMPRESA_ID
 AND c.codigo=SUBSTRING_INDEX(SUBSTRING_INDEX(m.detalle,'cuenta ',-1),';',1)
JOIN compras_clases_proveedores cp ON cp.cta_x_pagar_id=c.id
WHERE m.core_empresa_id=@EMPRESA_ID
  AND m.creado_por='Migracion Biable CxP 20260904'
GROUP BY m.core_tercero_id;
ALTER TABLE tmp_mig_clase_proveedor_tercero
  ADD PRIMARY KEY(core_tercero_id),ADD INDEX ix_tmp_mig_clase(clase_proveedor_id);

SET @terceros_total := (SELECT COUNT(*) FROM core_terceros
  WHERE core_empresa_id=@EMPRESA_ID);
SET @proveedores_antes := (SELECT COUNT(DISTINCT p.core_tercero_id)
  FROM compras_proveedores p
  JOIN core_terceros t ON t.id=p.core_tercero_id
  WHERE t.core_empresa_id=@EMPRESA_ID);
SET @clientes_antes := (SELECT COUNT(DISTINCT c.core_tercero_id)
  FROM vtas_clientes c
  JOIN core_terceros t ON t.id=c.core_tercero_id
  WHERE t.core_empresa_id=@EMPRESA_ID);

/* Validar todos los maestros usados como valores predeterminados. */
SET @actual :=
  (SELECT COUNT(*) FROM compras_clases_proveedores
   WHERE id=@CLASE_PROVEEDOR_GENERAL_ID AND estado='Activo')
  +(SELECT COUNT(*) FROM inv_bodegas
    WHERE id=@BODEGA_ID AND core_empresa_id=@EMPRESA_ID AND estado='Activo')
  +(SELECT COUNT(*) FROM compras_condiciones_pago
    WHERE id=@CONDICION_PAGO_COMPRAS_ID AND estado='Activo')
  +(SELECT COUNT(*) FROM vtas_clases_clientes
    WHERE id=@CLASE_CLIENTE_ID AND estado='Activo')
  +(SELECT COUNT(*) FROM vtas_listas_precios_encabezados
    WHERE id=@LISTA_PRECIOS_ID AND estado='Activo')
  +(SELECT COUNT(*) FROM vtas_listas_dctos_encabezados
    WHERE id=@LISTA_DESCUENTOS_ID AND estado='Activo')
  +(SELECT COUNT(*) FROM vtas_vendedores
    WHERE id=@VENDEDOR_ID AND estado='Activo')
  +(SELECT COUNT(*) FROM vtas_zonas
    WHERE id=@ZONA_ID AND estado='Activo')
  +(SELECT COUNT(*) FROM vtas_condiciones_pago
    WHERE id=@CONDICION_PAGO_VENTAS_ID AND estado='Activo');
SET @sql_validar := IF(@actual=9,'DO 0',
  CONCAT("SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Maestros predeterminados validos: ",@actual," de 9'"));
PREPARE validar FROM @sql_validar; EXECUTE validar; DEALLOCATE PREPARE validar;

SET @actual := (SELECT COUNT(*) FROM tmp_mig_clase_proveedor_tercero x
  LEFT JOIN compras_clases_proveedores cp ON cp.id=x.clase_proveedor_id
  LEFT JOIN contab_cuentas c ON c.id=cp.cta_x_pagar_id
  WHERE cp.id IS NULL OR c.codigo NOT LIKE '2335%');
SET @sql_validar := IF(@actual=0,'DO 0',
  CONCAT("SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Clases CxP invalidas: ",@actual,"'"));
PREPARE validar FROM @sql_validar; EXECUTE validar; DEALLOCATE PREPARE validar;

START TRANSACTION;

INSERT INTO compras_proveedores
(core_tercero_id,clase_proveedor_id,inv_bodega_id,liquida_impuestos,
 condicion_pago_id,codigo,estado,created_at,updated_at,declarante_renta,
 retencion_fuente_concepto_default_id)
SELECT t.id,COALESCE(x.clase_proveedor_id,@CLASE_PROVEEDOR_GENERAL_ID),
       @BODEGA_ID,1,@CONDICION_PAGO_COMPRAS_ID,'','Activo',NOW(),NOW(),
       'declarante',0
FROM core_terceros t
LEFT JOIN compras_proveedores p ON p.core_tercero_id=t.id
LEFT JOIN tmp_mig_clase_proveedor_tercero x ON x.core_tercero_id=t.id
WHERE t.core_empresa_id=@EMPRESA_ID AND p.id IS NULL;

INSERT INTO vtas_clientes
(core_tercero_id,encabezado_dcto_pp_id,clase_cliente_id,lista_precios_id,
 lista_descuentos_id,vendedor_id,inv_bodega_id,zona_id,liquida_impuestos,
 condicion_pago_id,cupo_credito,bloquea_por_cupo,bloquea_por_mora,estado,
 created_at,updated_at)
SELECT t.id,0,@CLASE_CLIENTE_ID,@LISTA_PRECIOS_ID,@LISTA_DESCUENTOS_ID,
       @VENDEDOR_ID,@BODEGA_ID,@ZONA_ID,1,@CONDICION_PAGO_VENTAS_ID,
       0,0,0,'Activo',NOW(),NOW()
FROM core_terceros t
LEFT JOIN vtas_clientes c ON c.core_tercero_id=t.id
WHERE t.core_empresa_id=@EMPRESA_ID AND c.id IS NULL;

/* Cobertura total y ausencia de duplicados por tercero. */
SET @faltan_proveedores := (SELECT COUNT(*) FROM core_terceros t
  LEFT JOIN compras_proveedores p ON p.core_tercero_id=t.id
  WHERE t.core_empresa_id=@EMPRESA_ID AND p.id IS NULL);
SET @faltan_clientes := (SELECT COUNT(*) FROM core_terceros t
  LEFT JOIN vtas_clientes c ON c.core_tercero_id=t.id
  WHERE t.core_empresa_id=@EMPRESA_ID AND c.id IS NULL);
SET @duplicados_proveedores := (SELECT COUNT(*) FROM (
  SELECT p.core_tercero_id FROM compras_proveedores p
  JOIN core_terceros t ON t.id=p.core_tercero_id
  WHERE t.core_empresa_id=@EMPRESA_ID
  GROUP BY p.core_tercero_id HAVING COUNT(*)>1) x);
SET @duplicados_clientes := (SELECT COUNT(*) FROM (
  SELECT c.core_tercero_id FROM vtas_clientes c
  JOIN core_terceros t ON t.id=c.core_tercero_id
  WHERE t.core_empresa_id=@EMPRESA_ID
  GROUP BY c.core_tercero_id HAVING COUNT(*)>1) x);
SET @sql_validar := IF(@faltan_proveedores=0 AND @faltan_clientes=0
  AND @duplicados_proveedores=0 AND @duplicados_clientes=0,
  'DO 0',"SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Cobertura o unicidad de terceros invalida'");
PREPARE validar FROM @sql_validar; EXECUTE validar; DEALLOCATE PREPARE validar;

SET @actual := (SELECT COUNT(*) FROM cxp_movimientos m
  LEFT JOIN compras_proveedores p ON p.core_tercero_id=m.core_tercero_id
  WHERE m.core_empresa_id=@EMPRESA_ID AND m.saldo_pendiente<>0 AND p.id IS NULL);
SET @sql_validar := IF(@actual=0,'DO 0',
  CONCAT("SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Cuentas por pagar sin proveedor: ",@actual,"'"));
PREPARE validar FROM @sql_validar; EXECUTE validar; DEALLOCATE PREPARE validar;

COMMIT;

SELECT @terceros_total terceros,
       @terceros_total-@proveedores_antes proveedores_creados,
       @terceros_total-@clientes_antes clientes_creados,
       (SELECT COUNT(*) FROM tmp_mig_clase_proveedor_tercero x
        JOIN compras_proveedores p ON p.core_tercero_id=x.core_tercero_id
         AND p.clase_proveedor_id=x.clase_proveedor_id)
         proveedores_clasificados_por_cxp;
