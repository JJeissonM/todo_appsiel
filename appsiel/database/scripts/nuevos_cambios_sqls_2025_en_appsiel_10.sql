-- 30 ENERO 2025
-- Se agregaron nuevas config para Ventas POS

-- Eliminar Campo cliente_input del Modelo Pedidos POS
DELETE FROM `sys_modelo_tiene_campos` WHERE `core_modelo_id` = 175 AND core_campo_id = 522;


-- 01 FEBRERO 2025
INSERT INTO `permissions` (`id`, `core_app_id`, `modelo_id`, `name`, `descripcion`, `url`, `parent`, `orden`, `enabled`, `fa_icon`, `created_at`, `updated_at`) VALUES (657, '20', '0', 'vtas_pos_anular_pedidos', 'Anular Pedidos', 'web', '0', '1', '0', '', '2025-02-01 00:00:00', NULL);

-- Permiso Reportes - OBLIGATORIO TODOS LOS CLIENTES
INSERT INTO `permissions` (`id`, `core_app_id`, `modelo_id`, `name`, `descripcion`, `url`, `parent`, `orden`, `enabled`, `fa_icon`, `created_at`, `updated_at`) VALUES (658, '20', '0', 'core_bloquear_menu_reportes', 'Bloquear menu reportes', 'web', '0', '1', '0', '', '2025-02-01 00:00:00', NULL);


-- 27 FEBRERO 5AM
INSERT INTO `sys_campos` (`id`, `descripcion`, `tipo`, `name`, `opciones`, `value`, `atributos`, `definicion`, `requerido`, `editable`, `unico`, `created_at`, `updated_at`) VALUES ('1531', 'Mostar promedio de calificaciones', 'select', 'mostrar_promedio_calificaciones', '{\"0\":\"No\",\"1\":\"Si\"}', 'null', '', '', '0', '1', '0', '2025-02-27 00:00:00', NULL);

INSERT INTO `sys_reporte_tiene_campos` (`orden`, `core_reporte_id`, `core_campo_id`) VALUES ('9', '17', '1531');

UPDATE `sys_campos` SET `opciones` = '{\"marca_agua\":\"Marca de agua\",\"dos_escudos\":\"Dos Escudos\",\"from_config\":\"Marca de agua (personalizado)\"}' WHERE `sys_campos`.`id` = 613;

UPDATE `sys_campos` SET `opciones` = '{\"\":\"\"}' WHERE `sys_campos`.`id` = 182;


-- Marzo 12
ALTER TABLE `cte_fuec_adicionales` DROP FOREIGN KEY cte_fuec_adicionales_conductor1_id_foreign;
ALTER TABLE `cte_fuec_adicionales` DROP FOREIGN KEY cte_fuec_adicionales_conductor2_id_foreign;
ALTER TABLE `cte_fuec_adicionales` DROP FOREIGN KEY cte_fuec_adicionales_conductor3_id_foreign;
ALTER TABLE `cte_fuec_adicionales` ADD FOREIGN KEY (`conductor1_id`) REFERENCES cte_conductors(id);
ALTER TABLE `cte_fuec_adicionales` ADD FOREIGN KEY (`conductor2_id`) REFERENCES cte_conductors(id);
ALTER TABLE `cte_fuec_adicionales` ADD FOREIGN KEY (`conductor3_id`) REFERENCES cte_conductors(id);
ALTER TABLE `cte_contratos` CHANGE `numero_fuec` `numero_fuec` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL;

ALTER TABLE `cte_contratos` ADD `descripcion_recorrido` TEXT NULL AFTER `updated_at`;
ALTER TABLE `cte_fuec_adicionales` ADD `descripcion_recorrido` TEXT NULL AFTER `updated_at`;


-- Abril 07 - Vtas. POS
ALTER TABLE `vtas_pos_doc_encabezados` ADD `uniqid` VARCHAR(255) NULL DEFAULT NULL AFTER `id`, ADD UNIQUE (`uniqid`);
ALTER TABLE `vtas_pos_doc_encabezados` ADD `efectivo_recibido` DOUBLE NOT NULL AFTER `valor_total`;

-- -- -- vtas_doc_encabezados
ALTER TABLE `vtas_doc_encabezados` ADD `efectivo_recibido` DOUBLE NOT NULL AFTER `valor_total`;

-- Abril 08 - Nomina
UPDATE `sys_campos` SET `opciones` = '{\"01\":\"1 - Dependiente\",\"12\":\"12 - Aprendices en etapa lectiva\",\"19\":\"19 - Aprendices en etapa productiva\",\"22\":\"22 - Profesor de establecimiento particular\",\"32\":\"32 - Cotizante miembro de la carrera diplomática o consular de un país extranjero o funcionario de organismo multilateral\",\"51\":\"51 - Trabajador de tiempo parcial\"}' WHERE `sys_campos`.`id` = 1145;

-- Colegios - 14 Abril
ALTER TABLE `sga_escala_valoracion` ADD `descripcion` TEXT NULL AFTER `escala_nacional`;
INSERT INTO `sys_modelo_tiene_campos` (`id`, `orden`, `core_modelo_id`, `core_campo_id`) VALUES (NULL, '6', '13', '8');

-- Cargar el archivo SQL --- sga_calificaciones_desempenio (1).sql --- para crear la tabla de calificaciones de desempeño y cargar los datos de la tabla sga_calificaciones_desempenio


-- 24 abril - Tesoreria - todos los clientes
UPDATE `sys_campos` SET `opciones` = '{\"\":\"\",\"recaudo-cartera\":\"Recaudo cartera clientes\",\"anticipo-clientes\":\"Anticipo clientes\",\"otros-recaudos\":\"Otros recaudos\",\"prestamo-recibido\":\"Préstamo financiero (CxP)\",\"pago-proveedores\":\"Pago a proveedores\",\"anticipo-proveedor\":\"Anticipo proveedor\",\"otros-pagos\":\"Otros pagos\",\"prestamo-entregado\":\"Préstamo financiero (Cartera CxC)\",\"traslado-efectivo\":\"Traslado\"}', `updated_at` = '2025-04-25 00:00:00' WHERE `sys_campos`.`id` = 230;

UPDATE `sys_campos` SET `opciones` = '{\"\":\"\",\"anticipo-clientes\":\"Anticipo\",\"otros-recaudos\":\"Otros recaudos\",\"prestamo-recibido\":\"Prestamo financiero (CxP)\"}', `updated_at` = '2025-04-25 00:00:00' WHERE `sys_campos`.`id` = 200;

UPDATE `sys_campos` SET `opciones` = '{\"otros-pagos\":\"Otros pagos\",\"anticipo-proveedor\":\"Anticipo proveedor\",\"prestamo-entregado\":\"Préstamo financiero (Cartera CxC)\"}', `updated_at` = '2025-04-25 00:00:00' WHERE `sys_campos`.`id` = 247;

-- Primero se agegan los nuevos tipos de motivos
ALTER TABLE `teso_motivos` CHANGE `teso_tipo_motivo` `teso_tipo_motivo` ENUM('Recaudo cartera','Otros recaudos','Pago proveedores','Otros pagos','Anticipo','Anticipo proveedor','Traslado','Pago anticipado','Prestamo financiero','recaudo-cartera','anticipo-clientes','otros-recaudos','prestamo-recibido','pago-proveedores','anticipo-proveedor','otros-pagos','prestamo-entregado','traslado-efectivo') CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;

-- Se actualizan los motivos de tesorería
UPDATE `teso_motivos` SET `teso_tipo_motivo` = 'recaudo-cartera', `updated_at` = '2025-04-25 00:00:00' WHERE `teso_tipo_motivo` = 'Recaudo cartera';
UPDATE `teso_motivos` SET `teso_tipo_motivo` = 'anticipo-clientes', `updated_at` = '2025-04-25 00:00:00' WHERE `teso_tipo_motivo` = 'Anticipo';
UPDATE `teso_motivos` SET `teso_tipo_motivo` = 'otros-recaudos', `updated_at` = '2025-04-25 00:00:00' WHERE `teso_tipo_motivo` = 'Otros recaudos';
UPDATE `teso_motivos` SET `teso_tipo_motivo` = 'prestamo-recibido', `updated_at` = '2025-04-25 00:00:00' WHERE `teso_tipo_motivo` = 'Prestamo financiero';
UPDATE `teso_motivos` SET `teso_tipo_motivo` = 'pago-proveedores', `updated_at` = '2025-04-25 00:00:00' WHERE `teso_tipo_motivo` = 'Pago proveedores';
UPDATE `teso_motivos` SET `teso_tipo_motivo` = 'anticipo-proveedor', `updated_at` = '2025-04-25 00:00:00' WHERE `teso_tipo_motivo` = 'Anticipo proveedor';
UPDATE `teso_motivos` SET `teso_tipo_motivo` = 'otros-pagos', `updated_at` = '2025-04-25 00:00:00' WHERE `teso_tipo_motivo` = 'Otros pagos';
UPDATE `teso_motivos` SET `teso_tipo_motivo` = 'prestamo-entregado', `updated_at` = '2025-04-25 00:00:00' WHERE `teso_tipo_motivo` = 'Pago anticipado';
UPDATE `teso_motivos` SET `teso_tipo_motivo` = 'traslado-efectivo', `updated_at` = '2025-04-25 00:00:00' WHERE `teso_tipo_motivo` = 'Traslado';
-- Luego se eliminan los antiguos
ALTER TABLE `teso_motivos` CHANGE `teso_tipo_motivo` `teso_tipo_motivo` ENUM('recaudo-cartera','anticipo-clientes','otros-recaudos','prestamo-recibido','pago-proveedores','anticipo-proveedor','otros-pagos','prestamo-entregado','traslado-efectivo') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;

-- 22 de mayo - Vtas. POS
INSERT INTO `permissions` (`id`, `core_app_id`, `modelo_id`, `name`, `descripcion`, `url`, `parent`, `orden`, `enabled`, `fa_icon`, `created_at`, `updated_at`) VALUES (NULL, '20', '0', 'bloquear_cierre_si_hay_pedidos_pendientes', 'Bloquear Cierre si hay pedidos Pendientes', 'web', '0', '1', '0', '', '2025-05-22 16:57:00', NULL);

-- 23 mayo - Imprimir codigos de barras
CREATE TABLE inv_barcodes_for_print ( `id` INT(10) NOT NULL AUTO_INCREMENT , `item_id` INT(10) NOT NULL , `label` VARCHAR(255) NOT NULL , `barcode` VARCHAR(15) NOT NULL , `reference` VARCHAR(20) NULL , `created_at` TIMESTAMP NOT NULL , `updated_at` TIMESTAMP NULL , PRIMARY KEY (`id`), INDEX (`item_id`)) ENGINE = InnoDB;
ALTER TABLE `inv_barcodes_for_print` ADD `uom_1` VARCHAR(10) NOT NULL AFTER `barcode`;
ALTER TABLE `inv_barcodes_for_print` ADD `size` VARCHAR(12) NOT NULL AFTER `reference`, ADD `supplier_code` VARCHAR(12) NOT NULL AFTER `size`;
ALTER TABLE `inv_barcodes_for_print` ADD `unit_price` DOUBLE NOT NULL AFTER `supplier_code`;

-- 29 de mayo - CxC
-- ------ Se agregaron NUEVAS config a Ventas y Tesoreria --------
ALTER TABLE `cxc_movimientos` ADD `detalle` TEXT NULL AFTER `estado`;
ALTER TABLE `cxp_movimientos` ADD `detalle` TEXT NULL AFTER `estado`;


-- 5 Junio CORE TERCEROS
ALTER TABLE `core_terceros` ADD `tax_level_code` VARCHAR(10) NULL DEFAULT 'O-47' AFTER `contab_cxp_cta_id`;
INSERT INTO `sys_campos` (`id`, `descripcion`, `tipo`, `name`, `opciones`, `value`, `atributos`, `definicion`, `requerido`, `editable`, `unico`, `created_at`, `updated_at`) VALUES ('1550', 'Responsabilidad Fiscal', 'select', 'tax_level_code', '{\"O-47\":\"Régimen Simple de tributación\",\"O-13\":\"Gran Contribuyente\",\"O-15\":\"Autorretenedor\",\"O-23\":\"Agente de retención IVA\",\"R-99-PN\":\"No aplica - Otros\"}', 'null', '', '', '0', '1', '0', '2025-06-05 20:02:05', NULL);
INSERT INTO `sys_modelo_tiene_campos` (`id`, `orden`, `core_modelo_id`, `core_campo_id`) VALUES (NULL, '55', '138', '1550');


-- 6 junio - NOMINA
INSERT INTO `sys_campos` (`id`, `descripcion`, `tipo`, `name`, `opciones`, `value`, `atributos`, `definicion`, `requerido`, `editable`, `unico`, `created_at`, `updated_at`) VALUES ('1551', 'Concepto DIAN relacionado', 'select', 'cpto_dian_id', 'nom_elect_cat_cptos_dian', 'null', '', '', '1', '0', '0', '2025-06-06 11:01:18', NULL);
UPDATE `sys_modelo_tiene_campos` SET `orden` = '12' WHERE `sys_modelo_tiene_campos`.`id` = 163;
INSERT INTO `sys_modelo_tiene_campos` (`id`, `orden`, `core_modelo_id`, `core_campo_id`) VALUES (NULL, '10', '84', '1551');
ALTER TABLE `nom_elect_cat_cptos_dian` ADD `descripcion` VARCHAR(100) NOT NULL AFTER `codigo`;
UPDATE `nom_elect_cat_cptos_dian` SET descripcion = codigo;
UPDATE `nom_conceptos` SET `cpto_dian_id` = '33' WHERE `cpto_dian_id` = 52;


-- 7 junio - Vtas. POS
ALTER TABLE `vtas_pos_doc_encabezados` ADD `valor_ajuste_al_peso` DOUBLE NULL AFTER `efectivo_recibido`, ADD `valor_total_cambio` DOUBLE NULL AFTER `valor_ajuste_al_peso`;
ALTER TABLE `vtas_pos_doc_encabezados` CHANGE `efectivo_recibido` `total_efectivo_recibido` DOUBLE NULL;

-- -- -- vtas_doc_encabezados
ALTER TABLE `vtas_doc_encabezados` ADD `valor_ajuste_al_peso` DOUBLE NULL AFTER `efectivo_recibido`, ADD `valor_total_cambio` DOUBLE NULL AFTER `valor_ajuste_al_peso`;
ALTER TABLE `vtas_doc_encabezados` CHANGE `efectivo_recibido` `total_efectivo_recibido` DOUBLE NULL;

-- Nuevas config en Vtas POS

-- 13 junio - Cambio unidad_medida1
UPDATE `sys_campos` SET `opciones` = '{\"\":\"\",\"94\":\"UND\",\"KGM\":\"KG\",\"GRM\":\"GR\",\"LTR\":\"LT\",\"ML\":\"MLT\",\"MTR\":\"M\",\"CMT\":\"CM\",\"MTK\":\"M2\",\"MTQ\":\"M3\"}', `updated_at` = '2025-06-13 00:00:00' WHERE `sys_campos`.`id` = 79;

UPDATE `inv_productos` SET `unidad_medida1` = '94' WHERE `unidad_medida1` = 'UND';
UPDATE `inv_productos` SET `unidad_medida1` = 'KGM' WHERE `unidad_medida1` = 'KG';
UPDATE `inv_productos` SET `unidad_medida1` = 'GRM' WHERE `unidad_medida1` = 'GR';
UPDATE `inv_productos` SET `unidad_medida1` = 'LTR' WHERE `unidad_medida1` = 'LT';
UPDATE `inv_productos` SET `unidad_medida1` = 'MTR' WHERE `unidad_medida1` = 'M';
UPDATE `inv_productos` SET `unidad_medida1` = 'CMT' WHERE `unidad_medida1` = 'CM';

-- --------------- A TODOS LOS CLIENTES CARGAR TABLAS DE items_mandatarios  ------------------
-- --------------- A TODOS LOS CLIENTES CARGAR TABLAS DE items_mandatarios  ------------------
-- --------------- A TODOS LOS CLIENTES CARGAR TABLAS DE items_mandatarios  ------------------

ALTER TABLE `inv_items_mandatarios` ADD `imagen` VARCHAR(100) NOT NULL DEFAULT '' AFTER `referencia`;

UPDATE `inv_items_mandatarios` SET `unidad_medida1` = '94' WHERE `unidad_medida1` = 'UND';
UPDATE `inv_items_mandatarios` SET `unidad_medida1` = 'KGM' WHERE `unidad_medida1` = 'KG';
UPDATE `inv_items_mandatarios` SET `unidad_medida1` = 'GRM' WHERE `unidad_medida1` = 'GR';
UPDATE `inv_items_mandatarios` SET `unidad_medida1` = 'LTR' WHERE `unidad_medida1` = 'LT';
UPDATE `inv_items_mandatarios` SET `unidad_medida1` = 'MTR' WHERE `unidad_medida1` = 'M';
UPDATE `inv_items_mandatarios` SET `unidad_medida1` = 'CMT' WHERE `unidad_medida1` = 'CM';

-- 16 de junio
ALTER TABLE `vtas_pos_doc_encabezados` ADD `valor_total_bolsas` DOUBLE NULL AFTER `valor_ajuste_al_peso`;

-- -- -- vtas_doc_encabezados
ALTER TABLE `vtas_doc_encabezados` ADD `valor_total_bolsas` DOUBLE NULL AFTER `valor_ajuste_al_peso`;

-- Nuevas config en Vtas POS

-- 01 de julio
UPDATE `sys_modelos` SET `descripcion` = 'Pagos generales' WHERE `sys_modelos`.`id` = 54;

-- 06 de julio - Para nuevo reporte de ventas POS
ALTER TABLE `inv_productos` ADD `prefijo_referencia_id` INT(10) NULL AFTER `mostrar_en_pagina_web`, ADD INDEX (`prefijo_referencia_id`);

-- -- -- Resúmen Diario de Ventas
ALTER TABLE `sys_reportes` ADD FOREIGN KEY (`core_app_id`) REFERENCES sys_aplicaciones(id);
INSERT INTO `sys_reportes` (`id`, `descripcion`, `core_app_id`, `url_form_action`, `estado`, `created_at`, `updated_at`) VALUES (75, 'Resúmen Diario de Ventas', '20', 'pos_resumen_diario', 'Activo', '2025-07-06 00:00:00', NULL);
INSERT INTO `sys_reporte_tiene_campos` (`orden`, `core_reporte_id`, `core_campo_id`) VALUES ('2', '75', '1314');
INSERT INTO `sys_reporte_tiene_campos` (`orden`, `core_reporte_id`, `core_campo_id`) VALUES ('4', '75', '1508');

-- 14 de agosto - Tesoreria - todos los clientes
UPDATE `sys_campos` SET `opciones` = '{\"\":\"\",\"anticipo-clientes\":\"Anticipo/Saldo a favor\",\"otros-recaudos\":\"Otros recaudos\",\"prestamo-recibido\":\"Prestamo financiero (CxP)\"}' WHERE `sys_campos`.`id` = 200;
UPDATE `sys_campos` SET `opciones` = '{\"\":\"\",\"recaudo-cartera\":\"Recaudo cartera clientes\",\"anticipo-clientes\":\"Anticipo/Saldo a favor clientes\",\"otros-recaudos\":\"Otros recaudos\",\"prestamo-recibido\":\"Préstamo financiero (CxP)\",\"pago-proveedores\":\"Pago a proveedores\",\"anticipo-proveedor\":\"Anticipo/Saldo a favor proveedor\",\"otros-pagos\":\"Otros pagos\",\"prestamo-entregado\":\"Préstamo financiero (Cartera CxC)\",\"traslado-efectivo\":\"Traslado\"}' WHERE `sys_campos`.`id` = 230;
UPDATE `sys_campos` SET `opciones` = '{\"otros-pagos\":\"Otros pagos\",\"anticipo-proveedor\":\"Anticipo/Saldo a favor proveedor\",\"prestamo-entregado\":\"Préstamo financiero (Cartera CxC)\"}' WHERE `sys_campos`.`id` = 247;
UPDATE `sys_campos` SET `opciones` = '{\"\":\"\",\"Cancelación documentos\":\"Cancelación documentos\",\"Anticipo\":\"Anticipo/Saldo a favor\"}' WHERE `sys_campos`.`id` = 265;


-- 30 de agosto
-- Reporte Etiquetas de codigos de barras - 
-- NOTA: Se debe cargar el archivo appsiel_1.0_inv_ indumentarias tablas.sql
DELETE FROM `sys_reporte_tiene_campos` WHERE `sys_reporte_tiene_campos`.`core_reporte_id` = 20 AND `sys_reporte_tiene_campos`.`core_campo_id` = 629;
DELETE FROM `sys_reporte_tiene_campos` WHERE `sys_reporte_tiene_campos`.`core_reporte_id` = 20 AND `sys_reporte_tiene_campos`.`core_campo_id` = 287;
DELETE FROM `sys_reporte_tiene_campos` WHERE `sys_reporte_tiene_campos`.`core_reporte_id` = 20 AND `sys_reporte_tiene_campos`.`core_campo_id` = 623;
DELETE FROM `sys_reporte_tiene_campos` WHERE `sys_reporte_tiene_campos`.`core_reporte_id` = 20 AND `sys_reporte_tiene_campos`.`core_campo_id` = 22;
DELETE FROM `sys_reporte_tiene_campos` WHERE `sys_reporte_tiene_campos`.`core_reporte_id` = 20 AND `sys_reporte_tiene_campos`.`core_campo_id` = 1453;
DELETE FROM `sys_reporte_tiene_campos` WHERE `sys_reporte_tiene_campos`.`core_reporte_id` = 20 AND `sys_reporte_tiene_campos`.`core_campo_id` = 1454;
DELETE FROM `sys_reporte_tiene_campos` WHERE `sys_reporte_tiene_campos`.`core_reporte_id` = 20 AND `sys_reporte_tiene_campos`.`core_campo_id` = 991;
DELETE FROM `sys_reporte_tiene_campos` WHERE `sys_reporte_tiene_campos`.`core_reporte_id` = 20 AND `sys_reporte_tiene_campos`.`core_campo_id` = 1455;
UPDATE `sys_reporte_tiene_campos` SET `orden` = '4' WHERE `sys_reporte_tiene_campos`.`core_reporte_id` = 20 AND `sys_reporte_tiene_campos`.`core_campo_id` = 1528;

INSERT INTO `sys_reporte_tiene_campos` (`orden`, `core_reporte_id`, `core_campo_id`) VALUES ('2', '20', '1551');

UPDATE `sys_campos` SET `opciones` = '{\"una\":\"Una\",\"segun_existencias\":\"Según existencias\",\"segun_ultima_factura_compras\":\"Según última fact. de compras\",\"cantidad_fija\":\"Cantidad fija\",\"entre_fechas\":\"Cant. ingresadas entre fechas\"}', `updated_at` = NULL WHERE `name` = 'cantidad_etiquetas_x_item';

INSERT INTO `sys_campos` (`id`, `descripcion`, `tipo`, `name`, `opciones`, `value`, `atributos`, `definicion`, `requerido`, `editable`, `unico`, `created_at`, `updated_at`) VALUES ('1551', 'Tipo de prenda', 'select', 'tipo_prenda_id', 'model_App\\Inventarios\\Indumentaria\\TipoPrenda', 'null', '', '', '0', '1', '0', '2025-08-30 14:36:17', NULL);
INSERT INTO `sys_campos` (`id`, `descripcion`, `tipo`, `name`, `opciones`, `value`, `atributos`, `definicion`, `requerido`, `editable`, `unico`, `created_at`, `updated_at`) VALUES ('1553', 'Fecha hasta', 'date', 'fecha_hasta', '', 'null', '{\"class\":\"form-control\"}', '', '0', '1', '0', '2025-08-30 14:40:52', NULL), ('1552', 'Fecha desde', 'date', 'fecha_desde', '', 'null', '{\"class\":\"form-control\"}', '', '0', '1', '0', '2025-08-30 14:40:52', NULL);


INSERT INTO `sys_reporte_tiene_campos` (`orden`, `core_reporte_id`, `core_campo_id`) VALUES ('16', '20', '1552');
INSERT INTO `sys_reporte_tiene_campos` (`orden`, `core_reporte_id`, `core_campo_id`) VALUES ('18', '20', '1553');


-- 26 sep 2025 - Vtas POS - Editar precio total en linea registro

INSERT INTO `permissions` (`id`, `core_app_id`, `modelo_id`, `name`, `descripcion`, `url`, `parent`, `orden`, `enabled`, `fa_icon`, `created_at`, `updated_at`) VALUES (NULL, '20', '0', 'editar_precio_total_en_linea_registro_factura_pos', 'Editar precio total en linea registro factura POS', 'web', '0', '1', '0', '', '2025-09-26 12:36:00', NULL);


-- 30 sept 2025 
-- PRENDAS
INSERT INTO `sys_campos` (`id`, `descripcion`, `tipo`, `name`, `opciones`, `value`, `atributos`, `definicion`, `requerido`, `editable`, `unico`, `created_at`, `updated_at`) VALUES ('1554', 'Agregar a la descripción de la prenda', 'select', 'mostrar_en_descripcion_de_prenda', '{"0":"No","1":"Si"}', 'null', '', '', '0', '1', '0', '2025-09-30 14:36:17', NULL);
INSERT INTO `sys_modelo_tiene_campos` (`id`, `orden`, `core_modelo_id`, `core_campo_id`) VALUES (NULL, '3', '327', '1554');

ALTER TABLE `inv_indum_tipos_materiales` ADD `mostrar_en_descripcion_de_prenda` BOOLEAN NULL AFTER `descripcion`;

INSERT INTO `sys_campos` (`id`, `descripcion`, `tipo`, `name`, `opciones`, `value`, `atributos`, `definicion`, `requerido`, `editable`, `unico`, `created_at`, `updated_at`) VALUES ('1555', 'Label descripción de la prenda', 'personalizado', 'lbl_descripcion_de_prenda', '', '<div style="border-style: outset; margin: 15px; padding: 10px; height: 40px; font-size: 1.2em;"> Descripción Prenda: <span id="lbl_descripcion"> </span> </div> <input type="hidden" id="descripcion" name="descripcion" />', '', '', '0', '1', '0', '2025-09-30 14:36:17', NULL);
INSERT INTO `sys_campos` (`id`, `descripcion`, `tipo`, `name`, `opciones`, `value`, `atributos`, `definicion`, `requerido`, `editable`, `unico`, `created_at`, `updated_at`) VALUES ('1556', 'Descripción/Detalle', 'bsText', 'descripcion_detalle', '', 'null', '', '', '1', '1', '0', '2025-09-30 14:36:17', NULL);
INSERT INTO `sys_modelo_tiene_campos` (`id`, `orden`, `core_modelo_id`, `core_campo_id`) VALUES (NULL, '99', '315', '1555');
INSERT INTO `sys_modelo_tiene_campos` (`id`, `orden`, `core_modelo_id`, `core_campo_id`) VALUES (NULL, '16', '315', '1556');
INSERT INTO `sys_modelo_tiene_campos` (`id`, `orden`, `core_modelo_id`, `core_campo_id`) VALUES (NULL, '0', '315', '140');
INSERT INTO `sys_modelo_tiene_campos` (`id`, `orden`, `core_modelo_id`, `core_campo_id`) VALUES (NULL, '1', '315', '97');

UPDATE `sys_campos` SET `atributos` = '' WHERE `name` = 'tipo_material_id';
UPDATE `sys_campos` SET `atributos` = '' WHERE `name` = 'tipo_prenda_id';
UPDATE `sys_campos` SET `atributos` = '' WHERE `name` = 'paleta_color_id';
UPDATE `sys_campos` SET `atributos` = '' WHERE `id` = 1384;

UPDATE `sys_modelo_tiene_campos` SET `orden` = '2' WHERE `core_modelo_id` = 315 AND `core_campo_id` = 1441;
UPDATE `sys_modelo_tiene_campos` SET `orden` = '14' WHERE `core_modelo_id` = 315 AND `core_campo_id` = 1440;
UPDATE `sys_modelo_tiene_campos` SET `orden` = '12' WHERE `core_modelo_id` = 315 AND `core_campo_id` = 1442;
DELETE FROM `sys_modelo_tiene_campos` WHERE `core_modelo_id` = 315 AND `core_campo_id` = 2;


-- 07 octubre 2025
-- NUEVA CONFIG FACTURACION ELECTRONICA - AVISO RESOLUCION


-- 08 octubre - Mejora reporte flujo de efectivo y formato estandar print pedidos de ventas
INSERT INTO `sys_campos` (`id`, `descripcion`, `tipo`, `name`, `opciones`, `value`, `atributos`, `definicion`, `requerido`, `editable`, `unico`, `created_at`, `updated_at`) VALUES ('1560', 'Tipo reporte', 'select', 'flujo_efectivo_tipo_reporte', '{\"columnario\":\"Columnario\",\"consolidado\":\"Consolidado\"}', 'null', '', '', '0', '1', '0', '2025-10-08 00:00:00', NULL);

INSERT INTO `sys_reporte_tiene_campos` (`orden`, `core_reporte_id`, `core_campo_id`) VALUES ('4', '30', '1560');

-- 11 octubre POS - Reporte Resumen Diario Ventas
DELETE FROM `sys_reporte_tiene_campos` WHERE `sys_reporte_tiene_campos`.`core_reporte_id` = 75 AND `sys_reporte_tiene_campos`.`core_campo_id` = 1314;
INSERT INTO `sys_reporte_tiene_campos` (`orden`, `core_reporte_id`, `core_campo_id`) VALUES ('1', '75', '473');
INSERT INTO `sys_reporte_tiene_campos` (`orden`, `core_reporte_id`, `core_campo_id`) VALUES ('2', '75', '474');

-- 13 octubre - formatos pedidos
INSERT INTO `sys_campos` (`id`, `descripcion`, `tipo`, `name`, `opciones`, `value`, `atributos`, `definicion`, `requerido`, `editable`, `unico`, `created_at`, `updated_at`) VALUES (NULL, 'Formato de impresión de Pedidos de ventas', 'select', 'formato_impresion_pedidos_ventas', '{\"pos\":\"POS\",\"estandar\":\"Estándar\",\"estandar2\":\"Estándar v2\"}', 'null', '', 'Usando para llenar el select de formatos de impresión de pedidos de ventas.\r\nFormatos disponibles\r\n\'pos\'=>\'POS\',\r\n\'estandar\'=>\'Estándar\',\r\n\'estandar2\'=>\'Estándar v2\'\r\n\r\nSe puede dejar solo los valores que utilice cada cliente y en el orden que se requiera.', '0', '1', '0', '2025-10-10 00:00:00', NULL);


-- 28 octubre - NOMINA
UPDATE `sys_campos` SET `opciones` = '{\"normal\":\"Normal\",\"labor_contratada\":\"Labor contratada\",\"por_turnos\":\"Por Turnos\"}', `updated_at` = '2025-10-28 00:00:00' WHERE `sys_campos`.`id` = 356;

INSERT INTO `sys_modelos` (`id`, `descripcion`, `modelo`, `name_space`, `modelo_relacionado`, `url_crear`, `url_edit`, `url_print`, `url_ver`, `enlaces`, `url_estado`, `url_eliminar`, `controller_complementario`, `url_form_create`, `home_miga_pan`, `ruta_storage_imagen`, `ruta_storage_archivo_adjunto`, `created_at`, `updated_at`) VALUES ('335', 'Contratos por Turnos', 'nom_contratos_turnos', 'App\\Nomina\\NomContratoPorTurno', '', '', '', '', '', '', '', '', '', '', '', '', '', '2025-10-28 00:00:00', NULL);
INSERT INTO `sys_modelos` (`id`, `descripcion`, `modelo`, `name_space`, `modelo_relacionado`, `url_crear`, `url_edit`, `url_print`, `url_ver`, `enlaces`, `url_estado`, `url_eliminar`, `controller_complementario`, `url_form_create`, `home_miga_pan`, `ruta_storage_imagen`, `ruta_storage_archivo_adjunto`, `created_at`, `updated_at`) VALUES ('336', 'Tipos de Turnos', 'nom_turnos_tipos', 'App\\Nomina\\TipoTurno', '', '', '', '', '', '', '', '', '', '', '', '', '', '2025-10-28 00:00:00', NULL);
INSERT INTO `sys_modelos` (`id`, `descripcion`, `modelo`, `name_space`, `modelo_relacionado`, `url_crear`, `url_edit`, `url_print`, `url_ver`, `enlaces`, `url_estado`, `url_eliminar`, `controller_complementario`, `url_form_create`, `home_miga_pan`, `ruta_storage_imagen`, `ruta_storage_archivo_adjunto`, `created_at`, `updated_at`) VALUES ('337', 'Registros de Turnos', 'nom_turnos_registros', 'App\\Nomina\\RegistroTurno', '', '', '', '', '', '', '', '', '', '', '', '', '', '2025-10-28 00:00:00', NULL);

INSERT INTO `permissions` (`id`, `core_app_id`, `modelo_id`, `name`, `descripcion`, `url`, `parent`, `orden`, `enabled`, `fa_icon`, `created_at`, `updated_at`) VALUES ('670', '17', '335', 'nom_contratos_por_turnos', 'Contratos por turnos', 'web', '225', '3', '1', '', '2025-10-28 00:00:00', NULL);

INSERT INTO `permissions` (`id`, `core_app_id`, `modelo_id`, `name`, `descripcion`, `url`, `parent`, `orden`, `enabled`, `fa_icon`, `created_at`, `updated_at`) VALUES ('671', '17', '336', 'nom_turnos_tipos', 'Tipos de turnos', 'web', '225', '7', '1', '', '2025-10-28 00:00:00', NULL);

INSERT INTO `permissions` (`id`, `core_app_id`, `modelo_id`, `name`, `descripcion`, `url`, `parent`, `orden`, `enabled`, `fa_icon`, `created_at`, `updated_at`) VALUES ('672', '17', '337', 'nom_turnos_registros', 'Registros de turnos', 'web', '217', '7', '1', '', '2025-10-28 00:00:00', NULL);

INSERT INTO `sys_modelo_tiene_campos` (`id`, `orden`, `core_modelo_id`, `core_campo_id`) VALUES (NULL, '2', '335', '650'), (NULL, '3', '335', '329'), (NULL, '6', '335', '986'), (NULL, '8', '335', '357'), (NULL, '14', '335', '644'), (NULL, '15', '335', '22'), (NULL, '16', '335', '360');

CREATE TABLE `nom_turnos_tipos` ( `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT , `descripcion` VARCHAR(255) NOT NULL , `valor` DOUBLE NOT NULL , `detalle` TEXT NULL , `estado` VARCHAR(20) NOT NULL , `created_at` TIMESTAMP NULL , `updated_at` TIMESTAMP NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;

CREATE TABLE `nom_turnos_registros` ( `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT , `contrato_id` INT(10) UNSIGNED NOT NULL , `tipo_turno_id` INT(10) UNSIGNED NOT NULL , `fecha` DATE NOT NULL , `valor` DOUBLE NOT NULL , `anotacion` TEXT NULL , `estado` VARCHAR(20) NOT NULL , `created_at` TIMESTAMP NULL , `updated_at` TIMESTAMP NULL , PRIMARY KEY (`id`), INDEX (`contrato_id`), INDEX (`tipo_turno_id`)) ENGINE = InnoDB;

ALTER TABLE `nom_turnos_registros` ADD FOREIGN KEY (`contrato_id`) REFERENCES nom_contratos(id);
ALTER TABLE `nom_turnos_registros` ADD FOREIGN KEY (`tipo_turno_id`) REFERENCES nom_turnos_tipos(id);

INSERT INTO `sys_modelo_tiene_campos` (`id`, `orden`, `core_modelo_id`, `core_campo_id`) VALUES (NULL, '2', '336', '2'), (NULL, '4', '336', '682'), (NULL, '6', '336', '257'), (NULL, '8', '336', '22');

INSERT INTO `sys_campos` (`id`, `descripcion`, `tipo`, `name`, `opciones`, `value`, `atributos`, `definicion`, `requerido`, `editable`, `unico`, `created_at`, `updated_at`) VALUES ('1565', 'Tipo de Turno', 'select', 'tipo_turno_id', 'model_App\\Nomina\\TipoTurno', 'null', '', '', '1', '1', '0', '2025-10-30 00:00:00', NULL);
INSERT INTO `sys_modelo_tiene_campos` (`id`, `orden`, `core_modelo_id`, `core_campo_id`) VALUES (NULL, '0', '337', '1142'), (NULL, '2', '337', '1565'), (NULL, '4', '337', '598');

ALTER TABLE `nom_contratos` ADD `turno_default_id` INT(10) NULL AFTER `tipo_cotizante`, ADD INDEX (`turno_default_id`);
ALTER TABLE `nom_contratos` CHANGE `turno_default_id` `turno_default_id` INT(10) UNSIGNED NULL DEFAULT NULL;

INSERT INTO `sys_campos` (`id`, `descripcion`, `tipo`, `name`, `opciones`, `value`, `atributos`, `definicion`, `requerido`, `editable`, `unico`, `created_at`, `updated_at`) VALUES ('1566', 'Turno por defecto', 'select', 'turno_default_id', 'model_App\\Nomina\\TipoTurno', 'null', '', '', '0', '1', '0', '2025-10-30 00:00:00', NULL);
INSERT INTO `sys_modelo_tiene_campos` (`id`, `orden`, `core_modelo_id`, `core_campo_id`) VALUES (NULL, '18', '335', '1566');


-- 15 nov - Inscripciones en linea
INSERT INTO `sys_campos` (`id`, `descripcion`, `tipo`, `name`, `opciones`, `value`, `atributos`, `definicion`, `requerido`, `editable`, `unico`, `created_at`, `updated_at`) VALUES ('1570', '¿El estudiante es de inclusión?', 'select', 'core_campo_id-ID', '{\"No\":\"No\",\"Si\":\"Sí\"}', 'null', '', '', '0', '1', '0', '2025-11-15 00:00:00', NULL);
INSERT INTO `sys_campos` (`id`, `descripcion`, `tipo`, `name`, `opciones`, `value`, `atributos`, `definicion`, `requerido`, `editable`, `unico`, `created_at`, `updated_at`) VALUES ('1571', 'Diagnóstico de inclusión', 'bsTextArea', 'core_campo_id-ID', '', 'null', '', '', '0', '1', '0', '2025-11-15 00:00:00', NULL);
INSERT INTO `sys_modelo_tiene_campos` (`id`, `orden`, `core_modelo_id`, `core_campo_id`) VALUES (NULL, '23', '323', '123'), (NULL, '24', '323', '1570'), (NULL, '26', '323', '1571');

-- 27 noviembre - Quitar create de modelo Matriculas
UPDATE `sys_modelos` SET `url_crear` = '' WHERE `sys_modelos`.`id` = 19;

-- 28 noviembre - Nuevo reporte Listado de FUECs
INSERT INTO `sys_reportes` (`id`, `descripcion`, `core_app_id`, `url_form_action`, `estado`, `created_at`, `updated_at`) VALUES (76, 'Listado de FUECs', '19', 'cte_fuecs_list', 'Activo', '2025-11-28 00:00:00', NULL);
INSERT INTO `sys_campos` (`id`, `descripcion`, `tipo`, `name`, `opciones`, `value`, `atributos`, `definicion`, `requerido`, `editable`, `unico`, `created_at`, `updated_at`) VALUES (1572, 'Vehículo', 'select', 'vehiculo_id', 'model_App\\Contratotransporte\\Vehiculo', 'null', '{\"class\":\"combobox\"}', '', '0', '1', '0', '2025-11-29 00:00:00', NULL);
INSERT INTO `sys_reporte_tiene_campos` (`orden`, `core_reporte_id`, `core_campo_id`) VALUES ('2', '76', '473'), ('4', '76', '474'), ('6', '76', '1572');

-- Nueva columna estado para la tabla Vehiculos
ALTER TABLE `cte_vehiculos` ADD `estado` VARCHAR(15) NOT NULL DEFAULT 'Activo' AFTER `propietario_id`;
INSERT INTO `sys_modelo_tiene_campos` (`id`, `orden`, `core_modelo_id`, `core_campo_id`) VALUES (NULL, '28', '202', '22');

-- 30 noviembre - NOMINA - Importar Turnos
ALTER TABLE `nom_contratos` ADD `fingerprint_reader_id` VARCHAR(30) NULL AFTER `tipo_cotizante`;
ALTER TABLE `nom_turnos_registros` CHANGE `tipo_turno_id` `tipo_turno_id` INT(10) UNSIGNED NULL;

INSERT INTO `sys_campos` (`id`, `descripcion`, `tipo`, `name`, `opciones`, `value`, `atributos`, `definicion`, `requerido`, `editable`, `unico`, `created_at`, `updated_at`) VALUES (1573, 'ID Lector Huellas', 'bsText', 'fingerprint_reader_id', '', 'null', '', '', '0', '1', '0', '2025-09-30 14:36:17', NULL);
INSERT INTO `sys_modelo_tiene_campos` (`id`, `orden`, `core_modelo_id`, `core_campo_id`) VALUES (NULL, '20', '335', '1573');

ALTER TABLE `nom_turnos_registros` ADD `checkin_time_1` TIME NULL AFTER `fecha`, ADD `checkout_time_1` TIME NULL AFTER `checkin_time_1`, ADD `checkin_time_2` TIME NULL AFTER `checkout_time_1`, ADD `checkout_time_2` TIME NULL AFTER `checkin_time_2`;
ALTER TABLE `nom_turnos_tipos` ADD `checkin_time_1` TIME NULL AFTER `descripcion`, ADD `checkout_time_1` TIME NULL AFTER `checkin_time_1`, ADD `checkin_time_2` TIME NULL AFTER `checkout_time_1`, ADD `checkout_time_2` TIME NULL AFTER `checkin_time_2`;

ALTER TABLE `nom_contratos` ADD FOREIGN KEY (`turno_default_id`) REFERENCES nom_turnos_tipos(id);

INSERT INTO `permissions` (`id`, `core_app_id`, `modelo_id`, `name`, `descripcion`, `url`, `parent`, `orden`, `enabled`, `fa_icon`, `created_at`, `updated_at`) VALUES (673, '17', '0', 'nomina.turnos.importar', 'Importar Turnos', 'nomina/turnos/importar', '217', '8', '1', '', '2025-11-30 00:00:00', NULL);
INSERT INTO `role_has_permissions` (`orden`, `permission_id`, `role_id`) VALUES ('0', '673', '1');
INSERT INTO `role_has_permissions` (`orden`, `permission_id`, `role_id`) VALUES ('0', '673', '3');

INSERT INTO `sys_campos` (`id`, `descripcion`, `tipo`, `name`, `opciones`, `value`, `atributos`, `definicion`, `requerido`, `editable`, `unico`, `created_at`, `updated_at`) VALUES (1574, 'Hora entrada 1', 'hora', 'checkin_time_1', ' ', 'null', '', '', '0', '1', '0', '2025-11-30 00:00:00', NULL);
INSERT INTO `sys_campos` (`id`, `descripcion`, `tipo`, `name`, `opciones`, `value`, `atributos`, `definicion`, `requerido`, `editable`, `unico`, `created_at`, `updated_at`) VALUES (1575, 'Hora salida 1', 'hora', 'checkout_time_1', ' ', 'null', '', '', '0', '1', '0', '2025-11-30 00:00:00', NULL);
INSERT INTO `sys_campos` (`id`, `descripcion`, `tipo`, `name`, `opciones`, `value`, `atributos`, `definicion`, `requerido`, `editable`, `unico`, `created_at`, `updated_at`) VALUES (1576, 'Hora entrada 2', 'hora', 'checkin_time_2', ' ', 'null', '', '', '0', '1', '0', '2025-11-30 00:00:00', NULL);
INSERT INTO `sys_campos` (`id`, `descripcion`, `tipo`, `name`, `opciones`, `value`, `atributos`, `definicion`, `requerido`, `editable`, `unico`, `created_at`, `updated_at`) VALUES (1577, 'Hora salida 2', 'hora', 'checkout_time_2', ' ', 'null', '', '', '0', '1', '0', '2025-11-30 00:00:00', NULL);

UPDATE `sys_modelo_tiene_campos` SET `orden` = '14' WHERE `core_modelo_id` = 336 AND `core_campo_id` = 22;
UPDATE `sys_modelo_tiene_campos` SET `orden` = '12' WHERE `core_modelo_id` = 336 AND `core_campo_id` = 257;
UPDATE `sys_modelo_tiene_campos` SET `orden` = '10' WHERE `core_modelo_id` = 336 AND `core_campo_id` = 682;
INSERT INTO `sys_modelo_tiene_campos` (`id`, `orden`, `core_modelo_id`, `core_campo_id`) VALUES (NULL, '3', '336', '97');
INSERT INTO `sys_modelo_tiene_campos` (`id`, `orden`, `core_modelo_id`, `core_campo_id`) VALUES (NULL, '6', '336', '1574');
INSERT INTO `sys_modelo_tiene_campos` (`id`, `orden`, `core_modelo_id`, `core_campo_id`) VALUES (NULL, '7', '336', '1575');
INSERT INTO `sys_modelo_tiene_campos` (`id`, `orden`, `core_modelo_id`, `core_campo_id`) VALUES (NULL, '8', '336', '1576');
INSERT INTO `sys_modelo_tiene_campos` (`id`, `orden`, `core_modelo_id`, `core_campo_id`) VALUES (NULL, '9', '336', '1577');

UPDATE `sys_modelos` SET `url_eliminar` = 'web_eliminar/id_fila' WHERE `sys_modelos`.`id` = 336;


-- Actualizar tabla migrations 4 diciembre
-- Se debe asignar el ultimo consecutivo de id a la tabla migrations para evitar conflictos con futuras migraciones
ALTER TABLE `migrations` CHANGE `id` `id` INT(10) UNSIGNED NOT NULL;
ALTER TABLE `migrations` CHANGE `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;


-- 5 diciembre - PDV Restaurante
ALTER TABLE `core_eav_valores` CHANGE `core_campo_id` `core_campo_id` INT(10) UNSIGNED NULL COMMENT 'Atributo en EAV';

-- 8 diciembre - IMPOCOMSUMO Por PDV
INSERT INTO `sys_campos` (`id`, `descripcion`, `tipo`, `name`, `opciones`, `value`, `atributos`, `definicion`, `requerido`, `editable`, `unico`, `created_at`, `updated_at`) VALUES (1578, 'Maneja Impoconsumo', 'select', 'maneja_impoconsumo', '{\"0\":\"No\",\"1\":\"Sí\"}', 'null', '', '', '0', '1', '0', '2025-12-08 00:00:00', NULL);
INSERT INTO `sys_modelo_tiene_campos` (`id`, `orden`, `core_modelo_id`, `core_campo_id`) VALUES (NULL, '20', '227', '1578');

ALTER TABLE `vtas_pos_puntos_de_ventas` ADD `maneja_impoconsumo` BOOLEAN NOT NULL DEFAULT FALSE AFTER `tipo_doc_app_default_id`;

-- 10 diciembre - NOMINA - Modelo Tipos de Turnos
UPDATE `sys_modelos` SET `modelo_relacionado` = 'tipos_turno' WHERE `sys_modelos`.`id` = 86;
ALTER TABLE `nom_turnos_tipos` ADD `orden` INT(2) NOT NULL DEFAULT '1' AFTER `detalle`;

--
-- Estructura de tabla para la tabla `nom_cargo_tipo_turno`
CREATE TABLE `nom_cargo_tipo_turno` (
  `id` int(10) UNSIGNED NOT NULL,
  `cargo_id` int(10) UNSIGNED NOT NULL,
  `tipo_turno_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
-- Indices de la tabla `nom_cargo_tipo_turno`
ALTER TABLE `nom_cargo_tipo_turno`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nom_cargo_tipo_turno_cargo_id_tipo_turno_id_unique` (`cargo_id`,`tipo_turno_id`),
  ADD KEY `nom_cargo_tipo_turno_tipo_turno_id_foreign` (`tipo_turno_id`);
-- AUTO_INCREMENT de la tabla `nom_cargo_tipo_turno`
ALTER TABLE `nom_cargo_tipo_turno`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
-- Filtros para la tabla `nom_cargo_tipo_turno`
ALTER TABLE `nom_cargo_tipo_turno`
  ADD CONSTRAINT `nom_cargo_tipo_turno_cargo_id_foreign` FOREIGN KEY (`cargo_id`) REFERENCES `nom_cargos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `nom_cargo_tipo_turno_tipo_turno_id_foreign` FOREIGN KEY (`tipo_turno_id`) REFERENCES `nom_turnos_tipos` (`id`) ON DELETE CASCADE;

-- NOMINA - Estado contrato por turnos
INSERT INTO `sys_campos` (`id`, `descripcion`, `tipo`, `name`, `opciones`, `value`, `atributos`, `definicion`, `requerido`, `editable`, `unico`, `created_at`, `updated_at`) VALUES (1579, 'Estado contrato', 'select', 'estado', '{\"Activo\":\"Activo\",\"Retirado\":\"Retirado\"}', 'null', '', '', '0', '1', '0', '2025-12-08 00:00:00', NULL);
DELETE FROM sys_modelo_tiene_campos WHERE `core_modelo_id` = 335 AND core_campo_id = 22;
INSERT INTO `sys_modelo_tiene_campos` (`id`, `orden`, `core_modelo_id`, `core_campo_id`) VALUES (NULL, '15', '335', '1579');


-- Impoconsumo - 6 enero 2026
ALTER TABLE `vtas_doc_registros` ADD `impuesto_id` INT(10) UNSIGNED NULL AFTER `inv_producto_id`, ADD INDEX (`impuesto_id`);
ALTER TABLE `vtas_doc_registros` ADD FOREIGN KEY (`impuesto_id`) REFERENCES contab_impuestos(id);
ALTER TABLE `vtas_movimientos` ADD `impuesto_id` INT(10) UNSIGNED NULL AFTER `inv_producto_id`, ADD INDEX (`impuesto_id`);
ALTER TABLE `vtas_movimientos` ADD FOREIGN KEY (`impuesto_id`) REFERENCES contab_impuestos(id);
ALTER TABLE `contab_movimientos` ADD `impuesto_id` INT(10) UNSIGNED NULL AFTER `inv_producto_id`, ADD INDEX (`impuesto_id`);
ALTER TABLE `contab_movimientos` ADD FOREIGN KEY (`impuesto_id`) REFERENCES contab_impuestos(id);

ALTER TABLE `vtas_pos_doc_registros` ADD `impuesto_id` INT(10) UNSIGNED NULL AFTER `inv_producto_id`, ADD INDEX (`impuesto_id`);
ALTER TABLE `vtas_pos_doc_registros` ADD FOREIGN KEY (`impuesto_id`) REFERENCES contab_impuestos(id);
ALTER TABLE `vtas_pos_movimientos` ADD `impuesto_id` INT(10) UNSIGNED NULL AFTER `inv_producto_id`, ADD INDEX (`impuesto_id`);
ALTER TABLE `vtas_pos_movimientos` ADD FOREIGN KEY (`impuesto_id`) REFERENCES contab_impuestos(id);

-- Ejecutar proceso: core_procesos/set_impuesto_id


-- 27 enero 2026 - Colegios - 
UPDATE `sys_modelos` SET `enlaces` = '{\"0\":{\"tag_html\":\"a\",\"title\":\"Consultar Cuestionarios\",\"url\":\"cuestionarios/revision?id=5&id_modelo=37\",\"color_bootstrap\":\"default\",\"faicon\":\"database\",\"size\":\"xs\"}}', `url_eliminar` = 'web_eliminar/id_fila' WHERE `sys_modelos`.`id` = 37;

DELETE FROM `sys_modelo_tiene_campos` WHERE `core_modelo_id` = 184 AND `core_campo_id` = 603;

-- 28 de Enero 2026 - BOT OSEI - Facturas De compras 
ALTER TABLE `compras_doc_registros` CHANGE `inv_producto_id` `inv_producto_id` INT(10) UNSIGNED NULL;
INSERT INTO `permissions` (`id`, `core_app_id`, `modelo_id`, `name`, `descripcion`, `url`, `parent`, `orden`, `enabled`, `fa_icon`, `created_at`, `updated_at`) VALUES (674, '9', '0', 'compras_procesos', 'Procesos', 'web', '0', '8', '1', '', '2026-01-28 22:07:13', NULL);
INSERT INTO `permissions` (`id`, `core_app_id`, `modelo_id`, `name`, `descripcion`, `url`, `parent`, `orden`, `enabled`, `fa_icon`, `created_at`, `updated_at`) VALUES (675, '9', '0', 'abrir_bot_osei', 'Abrir Bot OSEI', 'web', '0', '8', '1', '', '2026-01-28 22:07:13', NULL);

INSERT INTO `role_has_permissions` (`orden`, `permission_id`, `role_id`) VALUES ('0', '674', '1');
INSERT INTO `role_has_permissions` (`orden`, `permission_id`, `role_id`) VALUES ('0', '675', '1');

ALTER TABLE `compras_doc_registros` ADD `codigo_producto_xml` VARCHAR(255) NULL AFTER `inv_producto_id`, ADD `nombre_producto_xml` VARCHAR(255) NULL AFTER `codigo_producto_xml`;

-- 29 enero 2026 - NUEVA migration para colegios - 

-- Ejecutar composer dump-autoload

-- Ejecutar: php artisan migrate
-- Ejecutar: php artisan db:seed --class=IcfesQuestionBankSeeder
-- Ejecutar php artisan db:seed --class=CumplimientoGuiasReporteSeeder
INSERT INTO `sys_campos` (`id`, `descripcion`, `tipo`, `name`, `opciones`, `value`, `atributos`, `definicion`, `requerido`, `editable`, `unico`, `created_at`, `updated_at`) VALUES (1580, 'Curso', 'select', 'curso_id', '{\"\":\"\"}', 'null', '', '', '0', '1', '0', '2026-01-31 00:57:40', NULL);
INSERT INTO `sys_campos` (`id`, `descripcion`, `tipo`, `name`, `opciones`, `value`, `atributos`, `definicion`, `requerido`, `editable`, `unico`, `created_at`, `updated_at`) VALUES (1581, 'Asignatura ', 'select', 'asignatura_id', 'model_App\\Calificaciones\\Asignatura', 'null', '', '', '0', '1', '0', '2026-01-31 01:07:29', NULL);
UPDATE `sys_reporte_tiene_campos` SET `core_campo_id` = '1580' WHERE `core_reporte_id` = 76 AND `core_campo_id` = 182;
UPDATE `sys_reporte_tiene_campos` SET `core_campo_id` = '1581' WHERE `core_reporte_id` = 76 AND `core_campo_id` = 178;


-- 3 febrero - Permisos varios ventas y reportes
INSERT INTO `permissions` (`id`, `core_app_id`, `modelo_id`, `name`, `descripcion`, `url`, `parent`, `orden`, `enabled`, `fa_icon`, `created_at`, `updated_at`) VALUES (NULL, '13', '0', 'ventas.pedidos.show.generar_factura', 'Generar factura desde vista Show de Pedidos', 'web', '0', '99', '0', '', '2026-02-02 15:31:44', NULL);
INSERT INTO `permissions` (`id`, `core_app_id`, `modelo_id`, `name`, `descripcion`, `url`, `parent`, `orden`, `enabled`, `fa_icon`, `created_at`, `updated_at`) VALUES (NULL, '13', '0', 'ventas.index.crear_cliente', 'Crear cliente desde vista Index', 'web', '0', '99', '0', '', '2026-02-02 15:31:44', NULL);
INSERT INTO `permissions` (`id`, `core_app_id`, `modelo_id`, `name`, `descripcion`, `url`, `parent`, `orden`, `enabled`, `fa_icon`, `created_at`, `updated_at`) VALUES (NULL, '13', '0', 'ventas.index.crear_cotizacion', 'Crear cotización desde vista Index', 'web', '0', '99', '0', '', '2026-02-02 15:31:44', NULL);
INSERT INTO `permissions` (`id`, `core_app_id`, `modelo_id`, `name`, `descripcion`, `url`, `parent`, `orden`, `enabled`, `fa_icon`, `created_at`, `updated_at`) VALUES (NULL, '13', '0', 'ventas.pedidos.show.editar', 'Editar pedido desde vista Show de Pedidos', 'web', '0', '99', '0', '', '2026-02-02 15:31:44', NULL);
INSERT INTO `permissions` (`id`, `core_app_id`, `modelo_id`, `name`, `descripcion`, `url`, `parent`, `orden`, `enabled`, `fa_icon`, `created_at`, `updated_at`) VALUES (NULL, '13', '0', 'ventas.factura.anular', 'Anula factura de ventas', 'web', '0', '99', '0', '', '2026-02-02 15:31:44', NULL);
INSERT INTO `permissions` (`id`, `core_app_id`, `modelo_id`, `name`, `descripcion`, `url`, `parent`, `orden`, `enabled`, `fa_icon`, `created_at`, `updated_at`) VALUES (NULL, '13', '0', 'ventas.recaudo_cxc.crear', 'Crear recaudo de CXC', 'web', '0', '99', '0', '', '2026-02-02 15:31:44', NULL);
INSERT INTO `permissions` (`id`, `core_app_id`, `modelo_id`, `name`, `descripcion`, `url`, `parent`, `orden`, `enabled`, `fa_icon`, `created_at`, `updated_at`) VALUES (NULL, '13', '0', 'ventas.documento.modificar', 'Modificar documento de ventas', 'web', '0', '99', '0', '', '2026-02-02 15:31:44', NULL);
INSERT INTO `permissions` (`id`, `core_app_id`, `modelo_id`, `name`, `descripcion`, `url`, `parent`, `orden`, `enabled`, `fa_icon`, `created_at`, `updated_at`) VALUES (NULL, '13', '0', 'ventas.pedidos.show.imprimir', 'Imprimir pedido desde vista Show de Pedidos', 'web', '0', '99', '0', '', '2026-02-02 15:31:44', NULL);
INSERT INTO `permissions` (`id`, `core_app_id`, `modelo_id`, `name`, `descripcion`, `url`, `parent`, `orden`, `enabled`, `fa_icon`, `created_at`, `updated_at`) VALUES (NULL, '13', '0', 'vtas_documentos_facturacion', 'Ver Reporte Documentos de Facturación', 'web', '0', '99', '0', '', '2026-02-02 15:31:44', NULL);
INSERT INTO `permissions` (`id`, `core_app_id`, `modelo_id`, `name`, `descripcion`, `url`, `parent`, `orden`, `enabled`, `fa_icon`, `created_at`, `updated_at`) VALUES (NULL, '13', '0', 'vtas_reporte_movimientos', 'Ver Reporte Movimientos de ventas', 'web', '0', '99', '0', '', '2026-02-02 15:31:44', NULL);
INSERT INTO `permissions` (`id`, `core_app_id`, `modelo_id`, `name`, `descripcion`, `url`, `parent`, `orden`, `enabled`, `fa_icon`, `created_at`, `updated_at`) VALUES (NULL, '13', '0', 'vtas_lineas_de_movimiento_repetidas', 'Ver Reporte Movimientos de ventas repetidos', 'web', '0', '99', '0', '', '2026-02-02 15:31:44', NULL);
INSERT INTO `permissions` (`id`, `core_app_id`, `modelo_id`, `name`, `descripcion`, `url`, `parent`, `orden`, `enabled`, `fa_icon`, `created_at`, `updated_at`) VALUES (NULL, '13', '0', 'vtas_remisiones_sin_factura_real', 'Ver Reporte Remisiones SIN Facturas', 'web', '0', '99', '0', '', '2026-02-02 15:31:44', NULL);
INSERT INTO `permissions` (`id`, `core_app_id`, `modelo_id`, `name`, `descripcion`, `url`, `parent`, `orden`, `enabled`, `fa_icon`, `created_at`, `updated_at`) VALUES (NULL, '13', '0', 'vtas_reporte_ventas_por_vendedor', 'Ver Reporte Ventas por vendedor', 'web', '0', '99', '0', '', '2026-02-02 15:31:44', NULL);
INSERT INTO `permissions` (`id`, `core_app_id`, `modelo_id`, `name`, `descripcion`, `url`, `parent`, `orden`, `enabled`, `fa_icon`, `created_at`, `updated_at`) VALUES (NULL, '13', '0', 'cxc_estado_de_cuenta', 'Ver Reporte CxC > Estado de cuenta', 'web', '0', '99', '0', '', '2026-02-02 15:31:44', NULL);
INSERT INTO `permissions` (`id`, `core_app_id`, `modelo_id`, `name`, `descripcion`, `url`, `parent`, `orden`, `enabled`, `fa_icon`, `created_at`, `updated_at`) VALUES (NULL, '13', '0', 'vtas_reporte_rentabilidad', 'Ver Reporte Reporte de rentabilidad', 'web', '0', '99', '0', '', '2026-02-02 15:31:44', NULL);
INSERT INTO `permissions` (`id`, `core_app_id`, `modelo_id`, `name`, `descripcion`, `url`, `parent`, `orden`, `enabled`, `fa_icon`, `created_at`, `updated_at`) VALUES (NULL, '13', '0', 'vtas_reporte_ventas', 'Ver Reporte Reporte de ventas', 'web', '0', '99', '0', '', '2026-02-02 15:31:44', NULL);
INSERT INTO `permissions` (`id`, `core_app_id`, `modelo_id`, `name`, `descripcion`, `url`, `parent`, `orden`, `enabled`, `fa_icon`, `created_at`, `updated_at`) VALUES (NULL, '13', '0', 'vtas_precio_venta_por_producto', 'Ver Reporte Estadística de precios de venta', 'web', '0', '99', '0', '', '2026-02-02 15:31:44', NULL);
INSERT INTO `permissions` (`id`, `core_app_id`, `modelo_id`, `name`, `descripcion`, `url`, `parent`, `orden`, `enabled`, `fa_icon`, `created_at`, `updated_at`) VALUES (NULL, '20', '0', 'pos_resumen_diario', 'Ver Reporte Resúmen Diario de Ventas', 'web', '0', '99', '0', '', '2026-02-02 15:31:44', NULL);
INSERT INTO `permissions` (`id`, `core_app_id`, `modelo_id`, `name`, `descripcion`, `url`, `parent`, `orden`, `enabled`, `fa_icon`, `created_at`, `updated_at`) VALUES (NULL, '20', '0', 'pos_comprobante_informe_diario', 'Ver Reporte Comprobante de Informe Diario', 'web', '0', '99', '0', '', '2026-02-02 15:31:44', NULL);
INSERT INTO `permissions` (`id`, `core_app_id`, `modelo_id`, `name`, `descripcion`, `url`, `parent`, `orden`, `enabled`, `fa_icon`, `created_at`, `updated_at`) VALUES (NULL, '20', '0', 'vtas_reporte_pedidos', 'Ver Reporte Pedidos de ventas', 'web', '0', '99', '0', '', '2026-02-02 15:31:44', NULL);
INSERT INTO `permissions` (`id`, `core_app_id`, `modelo_id`, `name`, `descripcion`, `url`, `parent`, `orden`, `enabled`, `fa_icon`, `created_at`, `updated_at`) VALUES (NULL, '20', '0', 'pos_resumen_existencias', 'Ver Reporte Resumen Existencias', 'web', '0', '99', '0', '', '2026-02-02 15:31:44', NULL);
INSERT INTO `permissions` (`id`, `core_app_id`, `modelo_id`, `name`, `descripcion`, `url`, `parent`, `orden`, `enabled`, `fa_icon`, `created_at`, `updated_at`) VALUES (NULL, '20', '0', 'vtas_reporte_pedidos', 'Ver Reporte Pedidos de ventas', 'web', '0', '99', '0', '', '2026-02-02 15:31:44', NULL);
INSERT INTO `permissions` (`id`, `core_app_id`, `modelo_id`, `name`, `descripcion`, `url`, `parent`, `orden`, `enabled`, `fa_icon`, `created_at`, `updated_at`) VALUES (NULL, '20', '0', 'teso_movimiento_caja_bancos', 'Ver Reporte Movimiento de Caja/Bancos', 'web', '0', '99', '0', '', '2026-02-02 15:31:44', NULL);
INSERT INTO `permissions` (`id`, `core_app_id`, `modelo_id`, `name`, `descripcion`, `url`, `parent`, `orden`, `enabled`, `fa_icon`, `created_at`, `updated_at`) VALUES (NULL, '20', '0', 'pos_movimientos_ventas', 'Ver Reporte Resúmenes de ventas', 'web', '0', '99', '0', '', '2026-02-02 15:31:44', NULL);
INSERT INTO `permissions` (`id`, `core_app_id`, `modelo_id`, `name`, `descripcion`, `url`, `parent`, `orden`, `enabled`, `fa_icon`, `created_at`, `updated_at`) VALUES (NULL, '20', '0', 'vtas_reporte_rentabilidad', 'Ver Reporte Reporte de rentabilidad', 'web', '0', '99', '0', '', '2026-02-02 15:31:44', NULL);
INSERT INTO `permissions` (`id`, `core_app_id`, `modelo_id`, `name`, `descripcion`, `url`, `parent`, `orden`, `enabled`, `fa_icon`, `created_at`, `updated_at`) VALUES (NULL, '20', '0', 'cxc_documentos_pendientes', 'Ver Reporte CxC > Documentos pendientes por cobrar', 'web', '0', '99', '0', '', '2026-02-02 15:31:44', NULL);


-- Nomina 
ALTER TABLE `nom_agrupacion_tiene_conceptos` CHANGE `nom_concepto_id` `nom_concepto_id` INT(10) UNSIGNED NOT NULL;
INSERT INTO `sys_campos` (`id`, `descripcion`, `tipo`, `name`, `opciones`, `value`, `atributos`, `definicion`, `requerido`, `editable`, `unico`, `created_at`, `updated_at`) VALUES ('1582', 'Excluir de Nóm. Electrónica', 'select', 'excluir_documentos_nomina_electronica', '{\"0\":\"No\",\"1\":\"Sí\"}', 'null', '', '', '0', '1', '0', '2026-02-04 21:53:07', NULL);
INSERT INTO `sys_modelo_tiene_campos` (`id`, `orden`, `core_modelo_id`, `core_campo_id`) VALUES (NULL, '22', '83', '1582');


-- 8 febrero - CONTABILIDAD
ALTER TABLE `contab_cuenta_grupos` ADD `codigo` VARCHAR(10) NOT NULL AFTER `descripcion`;
ALTER TABLE `contab_cuenta_grupos` CHANGE `codigo` `codigo` VARCHAR(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL;

DELETE FROM `sys_campos` WHERE `sys_campos`.`id` = 590;
INSERT INTO `sys_campos` (`id`, `descripcion`, `tipo`, `name`, `opciones`, `value`, `atributos`, `definicion`, `requerido`, `editable`, `unico`, `created_at`, `updated_at`) VALUES ('590', 'Tiempo a liquidar', 'select', 'tiempo_a_liquidar', '{\"\":\"\",\"110\":\"Una Quincena (110 horas)\",\"220\":\"Un mes (220 horas)\",\"9999\":\"Órdenes de trabajo\"}', 'null', '', '', '1', '1', '0', '2019-12-20 00:36:57', '2025-01-21 18:08:27');