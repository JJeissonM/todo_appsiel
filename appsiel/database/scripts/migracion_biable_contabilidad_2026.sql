/* ============================================================================
   Migracion contable incremental Biable -> Appsiel
   Destino: u883985631_colegiobilingu (MariaDB)

   Alcance de escritura permanente:
     - core_terceros (solo terceros inexistentes)
     - contab_doc_encabezados
     - contab_movimientos
     - core_consecutivos_documentos

   No escribe tablas operativas de ventas, tesoreria, inventarios, cartera,
   cuentas por pagar, nomina, compras ni POS.

   Requiere snapshots previamente cargados en la base destino:
     - mig_biable_contab_saldos_20251231
     - mig_biable_contab_mov_2026
     - mig_biable_contab_terceros

   Idempotencia: elimina y reconstruye exclusivamente filas marcadas con
   @MARCA. Los terceros se insertan solo si numero_identificacion no existe.
   ============================================================================ */

USE `u883985631_colegiobilingu`;
SET NAMES utf8mb4;

SET @EMPRESA_ID := 1;
SET @MARCA := 'Migracion Biable Contabilidad 2026';
SET @ESPERADO_SALDOS_FUENTE := 3843;
SET @ESPERADO_LINEAS_2026 := 10243;
SET @ESPERADO_DOCUMENTOS_2026 := 1697;
/* Total luego de unificar codigos Biable que representan el mismo documento. */
SET @ESPERADO_DEBITOS_SI := 219552347104.56;
SET @ESPERADO_CREDITOS_SI := 219552347104.56;
SET @ESPERADO_DEBITOS_2026 := 51648121410.44;
SET @ESPERADO_CREDITOS_2026 := 51648121410.44;
SET @EMPRESA_NIT := (SELECT numero_identificacion FROM core_empresas WHERE id=@EMPRESA_ID);
SET @EMPRESA_TERCERO_ID := (
  SELECT MIN(id) FROM core_terceros
  WHERE core_empresa_id=@EMPRESA_ID AND numero_identificacion=@EMPRESA_NIT
);

/* Mapeo explicito del documento Biable al documento/transaccion Appsiel. */
DROP TEMPORARY TABLE IF EXISTS tmp_mig_tipo_documento;
CREATE TEMPORARY TABLE tmp_mig_tipo_documento (
  tipo_biable VARCHAR(2) NOT NULL PRIMARY KEY,
  core_tipo_transaccion_id INT UNSIGNED NOT NULL,
  core_tipo_doc_app_id INT UNSIGNED NOT NULL,
  prefijo_appsiel VARCHAR(40) NOT NULL
);
INSERT INTO tmp_mig_tipo_documento VALUES
  ('BN',14,125,'BN'),
  ('CC',25,17,'FC'),
  ('CE',57,23,'CE'),
  ('FV',52,49,'FEV'),
  ('LC',14,121,'LC'),
  ('LV',14,123,'LV'),
  ('NB',9,18,'NB'),
  ('NI',9,15,'NI'),
  ('NM',14,21,'NM'),
  ('NP',9,15,'NI'),
  ('PE',33,25,'PE'),
  ('PL',14,124,'PL'),
  ('RC',56,3,'RC');

/* Codigo Biable -> numero de identificacion. */
DROP TEMPORARY TABLE IF EXISTS tmp_mig_codigo_documento;
CREATE TEMPORARY TABLE tmp_mig_codigo_documento AS
SELECT
  codigos.codigo_biable,
  CASE
    WHEN codigos.codigo_biable IN ('CIERRE','NOMINAS','SI') THEN @EMPRESA_NIT
    WHEN maestros.nit_limpio REGEXP '^[0-9]+$' THEN CAST(maestros.nit_limpio AS UNSIGNED)
    WHEN codigos.codigo_biable REGEXP '^[0-9]+$' THEN CAST(codigos.codigo_biable AS UNSIGNED)
    ELSE NULL
  END AS numero_identificacion
FROM (
  SELECT DISTINCT codigo_tercero AS codigo_biable
  FROM mig_biable_contab_saldos_20251231
  UNION
  SELECT DISTINCT codigo_tercero
  FROM mig_biable_contab_mov_2026
) codigos
LEFT JOIN (
  SELECT codigo,
         MIN(NULLIF(REPLACE(REPLACE(REPLACE(REPLACE(nit,'.',''),'-',''),' ',''),',',''),'')) AS nit_limpio
  FROM mig_biable_contab_terceros
  GROUP BY codigo
) maestros ON maestros.codigo=codigos.codigo_biable;
ALTER TABLE tmp_mig_codigo_documento
  ADD PRIMARY KEY (codigo_biable),
  ADD INDEX ix_tmp_mig_numero_identificacion (numero_identificacion);

/* Datos representativos para crear terceros que aun no existan. */
DROP TEMPORARY TABLE IF EXISTS tmp_mig_terceros_candidatos;
CREATE TEMPORARY TABLE tmp_mig_terceros_candidatos AS
SELECT
  CAST(NULLIF(REPLACE(REPLACE(REPLACE(REPLACE(t.nit,'.',''),'-',''),' ',''),',',''),'') AS UNSIGNED) AS numero_identificacion,
  MIN(COALESCE(NULLIF(t.descripcion,''),CONCAT('Tercero Biable ',t.nit))) AS descripcion,
  MIN(COALESCE(t.nombres,'')) AS nombres,
  MIN(COALESCE(t.apellido1,'')) AS apellido1,
  MIN(COALESCE(t.apellido2,'')) AS apellido2,
  MIN(COALESCE(t.direccion1,'')) AS direccion1,
  MIN(COALESCE(t.direccion2,'')) AS direccion2,
  MIN(COALESCE(t.barrio,'')) AS barrio,
  MIN(COALESCE(t.telefono1,'')) AS telefono1,
  MIN(COALESCE(t.telefono2,'')) AS telefono2,
  MIN(COALESCE(t.email,'')) AS email,
  MIN(CASE WHEN t.nit_dv REGEXP '^[0-9]$' THEN CAST(t.nit_dv AS UNSIGNED) ELSE 0 END) AS digito_verificacion,
  MIN(CASE WHEN t.tipo_identifica IN ('1','2','3') THEN 'Persona' ELSE 'Entidad' END) AS tipo,
  MIN(CASE WHEN t.tipo_identifica IN ('1','2','3') THEN 1 ELSE 2 END) AS tipo_documento
FROM mig_biable_contab_terceros t
JOIN (SELECT DISTINCT numero_identificacion FROM tmp_mig_codigo_documento
      WHERE numero_identificacion IS NOT NULL) r
  ON r.numero_identificacion=CAST(NULLIF(REPLACE(REPLACE(REPLACE(REPLACE(t.nit,'.',''),'-',''),' ',''),',',''),'') AS UNSIGNED)
WHERE NULLIF(REPLACE(REPLACE(REPLACE(REPLACE(t.nit,'.',''),'-',''),' ',''),',',''),'') REGEXP '^[0-9]+$'
GROUP BY CAST(NULLIF(REPLACE(REPLACE(REPLACE(REPLACE(t.nit,'.',''),'-',''),' ',''),',',''),'') AS UNSIGNED);
ALTER TABLE tmp_mig_terceros_candidatos ADD PRIMARY KEY (numero_identificacion);

/* Completa candidatos numericos que no tengan fila util en el maestro. */
DROP TEMPORARY TABLE IF EXISTS tmp_mig_terceros_fallback;
CREATE TEMPORARY TABLE tmp_mig_terceros_fallback AS
SELECT DISTINCT d.numero_identificacion
FROM tmp_mig_codigo_documento d
LEFT JOIN tmp_mig_terceros_candidatos c
  ON c.numero_identificacion=d.numero_identificacion
WHERE d.numero_identificacion IS NOT NULL
  AND d.numero_identificacion<>@EMPRESA_NIT
  AND c.numero_identificacion IS NULL;

INSERT INTO tmp_mig_terceros_candidatos
(numero_identificacion,descripcion,nombres,apellido1,apellido2,direccion1,direccion2,
 barrio,telefono1,telefono2,email,digito_verificacion,tipo,tipo_documento)
SELECT f.numero_identificacion,
  CONCAT('Tercero migrado Biable ',f.numero_identificacion),'','','','','','','','','',
  0,'Entidad',2
FROM tmp_mig_terceros_fallback f;

DROP TEMPORARY TABLE IF EXISTS tmp_mig_tercero;
CREATE TEMPORARY TABLE tmp_mig_tercero (
  codigo_biable VARCHAR(20) NOT NULL PRIMARY KEY,
  numero_identificacion BIGINT UNSIGNED NOT NULL,
  core_tercero_id INT UNSIGNED NOT NULL,
  INDEX ix_tmp_mig_tercero_id (core_tercero_id)
);

/* Saldos oficiales al inicio del lapso 202601 = cierre de 2025. */
DROP TEMPORARY TABLE IF EXISTS tmp_mig_saldos_iniciales;
CREATE TEMPORARY TABLE tmp_mig_saldos_iniciales AS
SELECT
  s.cuenta,
  d.numero_identificacion,
  SUM(s.saldo) AS saldo
FROM mig_biable_contab_saldos_20251231 s
JOIN tmp_mig_codigo_documento d ON d.codigo_biable=s.codigo_tercero
GROUP BY s.cuenta,d.numero_identificacion
HAVING ABS(SUM(s.saldo))>0.005;
ALTER TABLE tmp_mig_saldos_iniciales
  ADD INDEX ix_tmp_mig_si_cuenta (cuenta),
  ADD INDEX ix_tmp_mig_si_tercero (numero_identificacion);

DROP TEMPORARY TABLE IF EXISTS tmp_mig_encabezados_anteriores;
CREATE TEMPORARY TABLE tmp_mig_encabezados_anteriores (
  id INT UNSIGNED NOT NULL PRIMARY KEY
);
INSERT INTO tmp_mig_encabezados_anteriores
SELECT id FROM contab_doc_encabezados
WHERE core_empresa_id=@EMPRESA_ID AND creado_por=@MARCA;

DROP TEMPORARY TABLE IF EXISTS tmp_mig_encabezados;
CREATE TEMPORARY TABLE tmp_mig_encabezados (
  id INT UNSIGNED NOT NULL PRIMARY KEY,
  tipo_biable VARCHAR(2) NOT NULL,
  core_tipo_transaccion_id INT UNSIGNED NOT NULL,
  core_tipo_doc_app_id INT UNSIGNED NOT NULL,
  consecutivo INT UNSIGNED NOT NULL,
  UNIQUE KEY uq_tmp_mig_doc
    (core_tipo_transaccion_id,core_tipo_doc_app_id,consecutivo)
);

DROP TEMPORARY TABLE IF EXISTS tmp_mig_consecutivos;
CREATE TEMPORARY TABLE tmp_mig_consecutivos (
  core_tipo_doc_app_id INT UNSIGNED NOT NULL PRIMARY KEY,
  consecutivo_final INT UNSIGNED NOT NULL
);

/* Validaciones de fuente antes de iniciar escrituras. */
SET @actual := (SELECT COUNT(*) FROM mig_biable_contab_saldos_20251231);
SET @sql_validar := IF(@actual=@ESPERADO_SALDOS_FUENTE,'DO 0',
  CONCAT("SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Saldos fuente inesperados: ",@actual,"'"));
PREPARE validar FROM @sql_validar; EXECUTE validar; DEALLOCATE PREPARE validar;

SET @actual := (SELECT COUNT(*) FROM mig_biable_contab_mov_2026);
SET @sql_validar := IF(@actual=@ESPERADO_LINEAS_2026,'DO 0',
  CONCAT("SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Lineas 2026 inesperadas: ",@actual,"'"));
PREPARE validar FROM @sql_validar; EXECUTE validar; DEALLOCATE PREPARE validar;

SET @actual := (SELECT COUNT(*) FROM (
  SELECT tipo_documento,consecutivo FROM mig_biable_contab_mov_2026
  GROUP BY tipo_documento,consecutivo) x);
SET @sql_validar := IF(@actual=@ESPERADO_DOCUMENTOS_2026,'DO 0',
  CONCAT("SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Documentos 2026 inesperados: ",@actual,"'"));
PREPARE validar FROM @sql_validar; EXECUTE validar; DEALLOCATE PREPARE validar;

SET @actual := (SELECT COUNT(*) FROM (
  SELECT DISTINCT m.tipo_documento FROM mig_biable_contab_mov_2026 m
  LEFT JOIN tmp_mig_tipo_documento t ON t.tipo_biable=m.tipo_documento
  WHERE t.tipo_biable IS NULL) x);
SET @sql_validar := IF(@actual=0,'DO 0',
  CONCAT("SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Tipos de documento sin mapear: ",@actual,"'"));
PREPARE validar FROM @sql_validar; EXECUTE validar; DEALLOCATE PREPARE validar;

SET @actual := (SELECT COUNT(*) FROM tmp_mig_codigo_documento WHERE numero_identificacion IS NULL);
SET @sql_validar := IF(@actual=0,'DO 0',
  CONCAT("SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Codigos de tercero sin mapear: ",@actual,"'"));
PREPARE validar FROM @sql_validar; EXECUTE validar; DEALLOCATE PREPARE validar;

SET @actual := (SELECT COUNT(*) FROM (
  SELECT DISTINCT x.cuenta
  FROM (SELECT cuenta FROM mig_biable_contab_saldos_20251231
        UNION SELECT cuenta FROM mig_biable_contab_mov_2026) x
  LEFT JOIN contab_cuentas c
    ON c.core_empresa_id=@EMPRESA_ID AND c.codigo=x.cuenta
  WHERE c.id IS NULL) y);
SET @sql_validar := IF(@actual=0,'DO 0',
  CONCAT("SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Cuentas contables faltantes: ",@actual,"'"));
PREPARE validar FROM @sql_validar; EXECUTE validar; DEALLOCATE PREPARE validar;

SET @actual := (SELECT COUNT(*) FROM core_tipos_docs_apps d
  RIGHT JOIN (SELECT DISTINCT core_tipo_doc_app_id FROM tmp_mig_tipo_documento
              UNION SELECT 19) x ON x.core_tipo_doc_app_id=d.id
  WHERE d.id IS NULL);
SET @sql_validar := IF(@actual=0 AND @EMPRESA_TERCERO_ID IS NOT NULL,'DO 0',
  "SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Falta documento Appsiel o tercero empresa'" );
PREPARE validar FROM @sql_validar; EXECUTE validar; DEALLOCATE PREPARE validar;

/* A partir de aqui solo DML InnoDB: ante error, la conexion revierte todo. */
START TRANSACTION;

INSERT INTO core_terceros
(descripcion,core_empresa_id,imagen,tipo,razon_social,nombre1,otros_nombres,
 apellido1,apellido2,id_tipo_documento_id,numero_identificacion,
 digito_verificacion,direccion1,direccion2,barrio,codigo_ciudad,codigo_postal,
 telefono1,telefono2,email,pagina_web,estado,user_id,contab_anticipo_cta_id,
 contab_cartera_cta_id,contab_cxp_cta_id,tax_level_code,creado_por,
 modificado_por,created_at,updated_at)
SELECT
  c.descripcion,@EMPRESA_ID,'',c.tipo,c.descripcion,c.nombres,'',c.apellido1,
  c.apellido2,c.tipo_documento,c.numero_identificacion,c.digito_verificacion,
  c.direccion1,c.direccion2,c.barrio,0,0,c.telefono1,c.telefono2,c.email,'',
  'Activo',1,0,0,0,'O-47',@MARCA,@MARCA,NOW(),NOW()
FROM tmp_mig_terceros_candidatos c
LEFT JOIN (SELECT numero_identificacion,MIN(id) id FROM core_terceros
           WHERE core_empresa_id=@EMPRESA_ID GROUP BY numero_identificacion) e
  ON e.numero_identificacion=c.numero_identificacion
WHERE e.id IS NULL;

INSERT INTO tmp_mig_tercero
(codigo_biable,numero_identificacion,core_tercero_id)
SELECT d.codigo_biable,d.numero_identificacion,MIN(t.id)
FROM tmp_mig_codigo_documento d
JOIN core_terceros t
  ON t.core_empresa_id=@EMPRESA_ID
 AND t.numero_identificacion=d.numero_identificacion
GROUP BY d.codigo_biable,d.numero_identificacion;

SET @actual := (SELECT COUNT(*) FROM tmp_mig_codigo_documento d
  LEFT JOIN tmp_mig_tercero t ON t.codigo_biable=d.codigo_biable
  WHERE t.core_tercero_id IS NULL);
SET @sql_validar := IF(@actual=0,'DO 0',
  CONCAT("SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Terceros Appsiel sin resolver: ",@actual,"'"));
PREPARE validar FROM @sql_validar; EXECUTE validar; DEALLOCATE PREPARE validar;

/* Reemplazo idempotente solo de la migracion contable. */
DELETE m FROM contab_movimientos m WHERE m.core_empresa_id=@EMPRESA_ID AND m.creado_por=@MARCA;
DELETE e FROM contab_doc_encabezados e
JOIN tmp_mig_encabezados_anteriores x ON x.id=e.id;

/* Un unico documento de saldos iniciales, SI 1. */
INSERT INTO contab_doc_encabezados
(core_tipo_transaccion_id,core_tipo_doc_app_id,consecutivo,fecha,
 core_empresa_id,core_tercero_id,codigo_referencia_tercero,documento_soporte,
 descripcion,valor_total,estado,creado_por,modificado_por,created_at,updated_at)
SELECT 10,19,1,'2025-12-31',@EMPRESA_ID,@EMPRESA_TERCERO_ID,'','BIABLE-SI-2025',
  'Saldos iniciales por cuenta y tercero al 31 de diciembre de 2025',
  SUM(GREATEST(saldo,0)),'Activo',@MARCA,@MARCA,NOW(),NOW()
FROM tmp_mig_saldos_iniciales;

/* Encabezados 2026: se crean solamente en contabilidad. */
INSERT INTO contab_doc_encabezados
(core_tipo_transaccion_id,core_tipo_doc_app_id,consecutivo,fecha,
 core_empresa_id,core_tercero_id,codigo_referencia_tercero,documento_soporte,
 descripcion,valor_total,estado,creado_por,modificado_por,created_at,updated_at)
SELECT
  td.core_tipo_transaccion_id,td.core_tipo_doc_app_id,
  CAST(m.consecutivo AS UNSIGNED),MIN(STR_TO_DATE(m.FECHA_DOC,'%Y%m%d')),
  @EMPRESA_ID,MIN(t.core_tercero_id),MIN(m.codigo_tercero),
  CONCAT('Biable ',m.tipo_documento,' ',m.consecutivo),
  COALESCE(NULLIF(MIN(m.detalle_documento),''),NULLIF(MIN(m.detalle1),''),
           CONCAT('Migracion Biable ',m.tipo_documento,' ',m.consecutivo)),
  SUM(m.VALOR_DEB),'Activo',@MARCA,@MARCA,NOW(),NOW()
FROM mig_biable_contab_mov_2026 m
JOIN tmp_mig_tipo_documento td ON td.tipo_biable=m.tipo_documento
JOIN tmp_mig_tercero t ON t.codigo_biable=m.codigo_tercero
GROUP BY m.tipo_documento,m.consecutivo,td.core_tipo_transaccion_id,
         td.core_tipo_doc_app_id;

INSERT INTO tmp_mig_encabezados
(id,tipo_biable,core_tipo_transaccion_id,core_tipo_doc_app_id,consecutivo)
SELECT e.id,'SI',e.core_tipo_transaccion_id,e.core_tipo_doc_app_id,e.consecutivo
FROM contab_doc_encabezados e
WHERE e.core_empresa_id=@EMPRESA_ID AND e.creado_por=@MARCA
  AND e.core_tipo_transaccion_id=10 AND e.core_tipo_doc_app_id=19
UNION ALL
SELECT e.id,m.tipo_documento,e.core_tipo_transaccion_id,e.core_tipo_doc_app_id,e.consecutivo
FROM contab_doc_encabezados e
JOIN tmp_mig_tipo_documento td
  ON td.core_tipo_transaccion_id=e.core_tipo_transaccion_id
 AND td.core_tipo_doc_app_id=e.core_tipo_doc_app_id
JOIN (SELECT DISTINCT tipo_documento,CAST(consecutivo AS UNSIGNED) consecutivo
      FROM mig_biable_contab_mov_2026) m
  ON m.tipo_documento=td.tipo_biable AND m.consecutivo=e.consecutivo
WHERE e.core_empresa_id=@EMPRESA_ID AND e.creado_por=@MARCA;

/* Lineas de saldos iniciales. Creditos se almacenan negativos en Appsiel. */
INSERT INTO contab_movimientos
(core_tipo_transaccion_id,core_tipo_doc_app_id,consecutivo,
 id_registro_doc_tipo_transaccion,fecha,core_empresa_id,core_tercero_id,
 codigo_referencia_tercero,documento_soporte,contab_cuenta_id,
 valor_operacion,valor_debito,valor_credito,valor_saldo,detalle_operacion,
 tipo_transaccion,inv_producto_id,impuesto_id,cantidad,tasa_impuesto,
 base_impuesto,valor_impuesto,fecha_vencimiento,inv_bodega_id,teso_caja_id,
 teso_cuenta_bancaria_id,estado,creado_por,modificado_por,created_at,updated_at)
SELECT
  10,19,1,e.id,'2025-12-31',@EMPRESA_ID,t.id,'','BIABLE-SI-2025',c.id,
  ABS(s.saldo),GREATEST(s.saldo,0),-GREATEST(-s.saldo,0),s.saldo,
  'Saldo inicial Biable al 31 de diciembre de 2025','',0,NULL,0,0,0,0,
  '2025-12-31',0,NULL,NULL,'Activo',@MARCA,@MARCA,NOW(),NOW()
FROM tmp_mig_saldos_iniciales s
JOIN (SELECT numero_identificacion,MIN(core_tercero_id) id
      FROM tmp_mig_tercero GROUP BY numero_identificacion) t
  ON t.numero_identificacion=s.numero_identificacion
JOIN contab_cuentas c ON c.core_empresa_id=@EMPRESA_ID AND c.codigo=s.cuenta
JOIN tmp_mig_encabezados e ON e.tipo_biable='SI';

/* Lineas contables desde el 1 de enero de 2026 hasta el corte del snapshot. */
INSERT INTO contab_movimientos
(core_tipo_transaccion_id,core_tipo_doc_app_id,consecutivo,
 id_registro_doc_tipo_transaccion,fecha,core_empresa_id,core_tercero_id,
 codigo_referencia_tercero,documento_soporte,contab_cuenta_id,
 valor_operacion,valor_debito,valor_credito,valor_saldo,detalle_operacion,
 tipo_transaccion,inv_producto_id,impuesto_id,cantidad,tasa_impuesto,
 base_impuesto,valor_impuesto,fecha_vencimiento,inv_bodega_id,teso_caja_id,
 teso_cuenta_bancaria_id,estado,creado_por,modificado_por,created_at,updated_at)
SELECT
  td.core_tipo_transaccion_id,td.core_tipo_doc_app_id,
  CAST(m.consecutivo AS UNSIGNED),e.id,STR_TO_DATE(m.FECHA_DOC,'%Y%m%d'),
  @EMPRESA_ID,t.core_tercero_id,m.codigo_tercero,
  LEFT(COALESCE(NULLIF(CONCAT_WS(' ',NULLIF(m.prefijo_proveedor,''),
       NULLIF(m.numero_proveedor,'')),''),NULLIF(m.documento_proveedor,''),
       CONCAT(m.tipo_documento,' ',m.consecutivo)),100),c.id,
  m.VALOR_DEB+m.VALOR_CRE,m.VALOR_DEB,-m.VALOR_CRE,
  m.VALOR_DEB-m.VALOR_CRE,
  LEFT(COALESCE(NULLIF(CONCAT_WS(' ',NULLIF(m.detalle1,''),NULLIF(m.detalle2,''),
       NULLIF(m.detalle_adicional,'')),''),NULLIF(m.detalle_documento,''),
       CONCAT('Migracion Biable ',m.tipo_documento,' ',m.consecutivo)),250),
  '',0,NULL,COALESCE(m.CANTIDAD,0),COALESCE(m.TASA_IMPRET,0),
  COALESCE(m.BASE_IVARET,0),0,
  CASE WHEN m.FECHA_VCTO REGEXP '^[0-9]{8}$' AND m.FECHA_VCTO<>'00000000'
       THEN STR_TO_DATE(m.FECHA_VCTO,'%Y%m%d')
       ELSE STR_TO_DATE(m.FECHA_DOC,'%Y%m%d') END,
  0,NULL,NULL,'Activo',@MARCA,@MARCA,NOW(),NOW()
FROM mig_biable_contab_mov_2026 m
JOIN tmp_mig_tipo_documento td ON td.tipo_biable=m.tipo_documento
JOIN tmp_mig_tercero t ON t.codigo_biable=m.codigo_tercero
JOIN contab_cuentas c ON c.core_empresa_id=@EMPRESA_ID AND c.codigo=m.cuenta
JOIN tmp_mig_encabezados e
  ON e.tipo_biable=m.tipo_documento
 AND e.core_tipo_transaccion_id=td.core_tipo_transaccion_id
 AND e.core_tipo_doc_app_id=td.core_tipo_doc_app_id
 AND e.consecutivo=CAST(m.consecutivo AS UNSIGNED);

/* Consecutivo final por tipo de documento, sin disminuir valores existentes. */
INSERT INTO tmp_mig_consecutivos VALUES (19,1);
INSERT INTO tmp_mig_consecutivos
SELECT td.core_tipo_doc_app_id,MAX(CAST(m.consecutivo AS UNSIGNED))
FROM mig_biable_contab_mov_2026 m
JOIN tmp_mig_tipo_documento td ON td.tipo_biable=m.tipo_documento
GROUP BY td.core_tipo_doc_app_id
ON DUPLICATE KEY UPDATE consecutivo_final=GREATEST(consecutivo_final,VALUES(consecutivo_final));

UPDATE core_consecutivos_documentos c
JOIN tmp_mig_consecutivos m ON m.core_tipo_doc_app_id=c.core_documento_app_id
SET c.consecutivo_actual=GREATEST(c.consecutivo_actual,m.consecutivo_final),
    c.updated_at=NOW()
WHERE c.core_empresa_id=@EMPRESA_ID;

INSERT INTO core_consecutivos_documentos
(core_empresa_id,core_documento_app_id,consecutivo_actual,created_at,updated_at)
SELECT @EMPRESA_ID,m.core_tipo_doc_app_id,m.consecutivo_final,NOW(),NOW()
FROM tmp_mig_consecutivos m
LEFT JOIN core_consecutivos_documentos c
  ON c.core_empresa_id=@EMPRESA_ID
 AND c.core_documento_app_id=m.core_tipo_doc_app_id
WHERE c.id IS NULL;

/* Validaciones bloqueantes dentro de la transaccion. */
SET @actual := (SELECT COUNT(*) FROM contab_doc_encabezados
  WHERE core_empresa_id=@EMPRESA_ID AND creado_por=@MARCA);
SET @sql_validar := IF(@actual=@ESPERADO_DOCUMENTOS_2026+1,'DO 0',
  CONCAT("SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Encabezados cargados: ",@actual,"'"));
PREPARE validar FROM @sql_validar; EXECUTE validar; DEALLOCATE PREPARE validar;

SET @actual := (SELECT COUNT(*) FROM contab_movimientos
  WHERE core_empresa_id=@EMPRESA_ID AND creado_por=@MARCA AND fecha>='2026-01-01');
SET @sql_validar := IF(@actual=@ESPERADO_LINEAS_2026,'DO 0',
  CONCAT("SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Lineas 2026 cargadas: ",@actual,"'"));
PREPARE validar FROM @sql_validar; EXECUTE validar; DEALLOCATE PREPARE validar;

SET @actual := (SELECT COUNT(*) FROM contab_movimientos
  WHERE core_empresa_id=@EMPRESA_ID AND creado_por=@MARCA AND fecha='2025-12-31');
SET @esperado := (SELECT COUNT(*) FROM tmp_mig_saldos_iniciales);
SET @sql_validar := IF(@actual=@esperado,'DO 0',
  CONCAT("SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Lineas SI cargadas: ",@actual," de ",@esperado,"'"));
PREPARE validar FROM @sql_validar; EXECUTE validar; DEALLOCATE PREPARE validar;

SET @deb_si := (SELECT ROUND(SUM(valor_debito),2) FROM contab_movimientos
  WHERE core_empresa_id=@EMPRESA_ID AND creado_por=@MARCA AND fecha='2025-12-31');
SET @cre_si := (SELECT ROUND(-SUM(valor_credito),2) FROM contab_movimientos
  WHERE core_empresa_id=@EMPRESA_ID AND creado_por=@MARCA AND fecha='2025-12-31');
SET @deb_2026 := (SELECT ROUND(SUM(valor_debito),2) FROM contab_movimientos
  WHERE core_empresa_id=@EMPRESA_ID AND creado_por=@MARCA AND fecha>='2026-01-01');
SET @cre_2026 := (SELECT ROUND(-SUM(valor_credito),2) FROM contab_movimientos
  WHERE core_empresa_id=@EMPRESA_ID AND creado_por=@MARCA AND fecha>='2026-01-01');
SELECT @deb_si AS validacion_debitos_si,@cre_si AS validacion_creditos_si,
       @deb_2026 AS validacion_debitos_2026,@cre_2026 AS validacion_creditos_2026;
SET @sql_validar := IF(
  ABS(@deb_si-@ESPERADO_DEBITOS_SI)<=0.05
  AND ABS(@cre_si-@ESPERADO_CREDITOS_SI)<=0.05
  AND ABS(@deb_2026-@ESPERADO_DEBITOS_2026)<=0.05
  AND ABS(@cre_2026-@ESPERADO_CREDITOS_2026)<=0.05,
  'DO 0',"SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Totales contables no coinciden'" );
PREPARE validar FROM @sql_validar; EXECUTE validar; DEALLOCATE PREPARE validar;

SET @actual := (SELECT COUNT(*) FROM (
  SELECT core_tipo_transaccion_id,core_tipo_doc_app_id,consecutivo,
         ROUND(SUM(valor_saldo),2) diferencia
  FROM contab_movimientos
  WHERE core_empresa_id=@EMPRESA_ID AND creado_por=@MARCA
  GROUP BY core_tipo_transaccion_id,core_tipo_doc_app_id,consecutivo
  HAVING ABS(ROUND(SUM(valor_saldo),2))>0.01) x);
SET @sql_validar := IF(@actual=0,'DO 0',
  CONCAT("SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Documentos descuadrados: ",@actual,"'"));
PREPARE validar FROM @sql_validar; EXECUTE validar; DEALLOCATE PREPARE validar;

COMMIT;

/* Resumen auditable. */
SELECT 'terceros_creados' operacion,COUNT(*) cantidad,NULL debitos,NULL creditos
FROM core_terceros WHERE core_empresa_id=@EMPRESA_ID AND creado_por=@MARCA
UNION ALL
SELECT 'encabezados_saldo_inicial',COUNT(*),SUM(valor_total),SUM(valor_total)
FROM contab_doc_encabezados
WHERE core_empresa_id=@EMPRESA_ID AND creado_por=@MARCA AND fecha='2025-12-31'
UNION ALL
SELECT 'lineas_saldo_inicial',COUNT(*),SUM(valor_debito),-SUM(valor_credito)
FROM contab_movimientos
WHERE core_empresa_id=@EMPRESA_ID AND creado_por=@MARCA AND fecha='2025-12-31'
UNION ALL
SELECT 'encabezados_2026',COUNT(*),SUM(valor_total),SUM(valor_total)
FROM contab_doc_encabezados
WHERE core_empresa_id=@EMPRESA_ID AND creado_por=@MARCA AND fecha>='2026-01-01'
UNION ALL
SELECT 'lineas_2026',COUNT(*),SUM(valor_debito),-SUM(valor_credito)
FROM contab_movimientos
WHERE core_empresa_id=@EMPRESA_ID AND creado_por=@MARCA AND fecha>='2026-01-01';

SELECT e.tipo_biable,td.prefijo_appsiel,COUNT(DISTINCT e.id) documentos,COUNT(m.id) lineas,
       MAX(CAST(e.consecutivo AS UNSIGNED)) consecutivo_final,
       SUM(m.valor_debito) debitos,-SUM(m.valor_credito) creditos
FROM tmp_mig_encabezados e
JOIN contab_movimientos m
  ON m.core_tipo_transaccion_id=e.core_tipo_transaccion_id
 AND m.core_tipo_doc_app_id=e.core_tipo_doc_app_id
 AND m.consecutivo=e.consecutivo
JOIN tmp_mig_tipo_documento td ON td.tipo_biable=e.tipo_biable
WHERE e.tipo_biable<>'SI'
GROUP BY e.tipo_biable,td.prefijo_appsiel
ORDER BY e.tipo_biable;
