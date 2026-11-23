<?php

namespace App\FacturacionElectronica\OSEI;

use App\FacturacionElectronica\NotaAjusteDocSoporte as ModeloNotaAjuste;

/**
 * Nota de ajuste al documento soporte (DIAN CreditNote 95).
 * Reutiliza supplier, envío y manejo de errores del documento soporte.
 */
class NotaAjusteDocSoporte extends DocSoporte
{
   protected $llave_raiz = 'support_doc_note';

   function __construct($doc_encabezado, $tipo_transaccion = 'support_doc_note')
   {
      parent::__construct($doc_encabezado, $tipo_transaccion);

      $this->url_emision = rtrim(config('facturacion_electronica.url_documento_soporte'), '/') . '/adjustment_notes';
   }

   public function procesar_envio_factura()
   {
      $relacionado = $this->get_documento_relacionado();

      if ($relacionado === null) {
         return (object)[
            'tipo' => 'mensaje_error',
            'contenido' => 'El documento soporte no ha sido aceptado por la DIAN. Envíelo y espere su aceptación antes de enviar la nota de ajuste.'
         ];
      }

      $payload = $this->armar_payload($relacionado);

      if (!is_array($payload)) {
         return $payload; // mensaje_error
      }

      return $this->enviar_documento_electronico(config('facturacion_electronica.tokenPassword'), $payload);
   }

   /** La nota se envía (y se consulta el PDF) con el prefijo de su tipo de documento. */
   public function prefijo_osei()
   {
      return $this->doc_encabezado->tipo_documento_app->prefijo;
   }

   /**
    * Datos del último envío VÁLIDO del documento soporte: cuds (cufe) y, del JSON
    * que se le envió a OSEI, prefijo, número y fecha. Null si no hay ninguno.
    */
   public function get_documento_relacionado()
   {
      $ds = $this->doc_encabezado->doc_soporte;

      if ($ds === null) {
         return null;
      }

      $valido = $ds->resultados_envios_fe_doc_soporte()->filter(function ($r) {
         return $r->esValidoDian == 1;
      })->last();

      if ($valido === null) {
         return null;
      }

      $enviado = json_decode($valido->nombre, true);
      $doc = isset($enviado['support_doc']) ? $enviado['support_doc'] : [];

      return [
         'prefix' => isset($doc['resolution']['prefix']) ? $doc['resolution']['prefix'] : '',
         'number' => isset($doc['number']) ? $doc['number'] : $ds->consecutivo,
         'cuds' => $valido->cufe,
         'issue_date' => isset($doc['issue_date']) ? $doc['issue_date'] : date_format(date_create($ds->fecha), 'd/m/Y'),
      ];
   }

   /** Arreglo PHP listo para OSEI, o mensaje_error si el supplier no se pudo armar. */
   public function armar_payload(array $relacionado)
   {
      $nota = $this->doc_encabezado;

      $supplier = json_decode($this->get_datos_cliente(), true);
      if (!is_array($supplier)) {
         return (object)[
            'tipo' => 'mensaje_error',
            'contenido' => 'Error interno: los datos del proveedor no generan un JSON válido (revise comillas en el nombre o la dirección).'
         ];
      }

      $descripcion_concepto = $this->descripcion_concepto((int)$nota->nota_ajuste_concepto);
      if (trim((string)$nota->descripcion) != '') {
         $descripcion_concepto .= ': ' . trim($nota->descripcion);
      }

      $payment_means_type = $nota->forma_pago == 'credito' ? 'CREDITO' : 'DEBITO';

      $notes = '-';
      if (trim((string)$nota->descripcion) != '') {
         $notes = trim($nota->descripcion);
      }

      return [
         $this->llave_raiz => [
            'env' => $this->env,
            'authorization_token' => config('facturacion_electronica.tokenEmpresa'),
            'prefix' => $nota->tipo_documento_app->prefijo,
            'number' => (int)$nota->consecutivo,
            'issue_date' => date_format(date_create($nota->fecha), 'd/m/Y'),
            'currency' => 'COP',
            'payment_means' => 'MUTUAL_AGREEMENT',
            'payment_means_type' => $payment_means_type,
            'payment_date' => date_format(date_create($nota->fecha_vencimiento), 'd/m/Y'),
            'discrepancy' => [
               'code' => (int)$nota->nota_ajuste_concepto,
               'description' => mb_substr($descripcion_concepto, 0, 500, 'UTF-8'),
            ],
            'related_document' => $relacionado,
            'supplier' => $supplier,
            'items' => $this->get_items_nota(),
            'anotation' => $notes,
         ]
      ];
   }

   /**
    * Valores absolutos (las líneas de la nota restan, OSEI los quiere positivos y ya netos).
    * Conceptos 1 y 2 ajustan cantidad: price = base / cantidad. Los demás (3, 4, 5): 1 x base.
    */
   public function get_items_nota()
   {
      $ajusta_cantidad = in_array((int)$this->doc_encabezado->nota_ajuste_concepto, [1, 2]);
      $items = [];

      foreach ($this->doc_encabezado->lineas_registros as $linea) {
         $base = abs($linea->base_impuesto);
         $cantidad = abs($linea->cantidad);

         if ($ajusta_cantidad && $cantidad != 0) {
            $quantity = $cantidad;
            $price = $base / $cantidad;
         } else {
            $quantity = 1;
            $price = $base;
         }

         $tax_category = config('ventas.etiqueta_impuesto_principal');
         if ($linea->item->impuesto->tax_category != null && $linea->item->impuesto->tax_category != '') {
            $tax_category = $linea->item->impuesto->tax_category;
         }

         $items[] = [
            'sku' => (string)$linea->item->id,
            'u.m' => $linea->item->unidad_medida1,
            'description' => $linea->item->descripcion,
            'quantity' => (float)number_format($quantity, $this->cantidadDecimales, '.', ''),
            'price' => (float)number_format($price, $this->cantidadDecimales, '.', ''),
            'taxes' => [['tax_rate' => $linea->tasa_impuesto + 0, 'tax_category' => $tax_category]],
         ];
      }

      return $items;
   }

   protected function descripcion_concepto($codigo)
   {
      $conceptos = ModeloNotaAjuste::CONCEPTOS;

      return isset($conceptos[$codigo]) ? $conceptos[$codigo] : '';
   }
}
