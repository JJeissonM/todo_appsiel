$(document).ready(function(){

  function ocultar_campo_formulario( obj_input, valor_requerido )
  {
    obj_input.prop( 'required', valor_requerido);
    obj_input.hide();
    obj_input.parent().prev('label').text('');
  }

  function mostrar_campo_formulario( obj_input, texto_lbl, valor_requerido )
  {
    obj_input.prop( 'required', valor_requerido );
    obj_input.show();
    obj_input.parent().prev('label').text( texto_lbl );
  }

	ocultar_campo_formulario( $('#teso_caja_id'), false );
  ocultar_campo_formulario( $('#teso_cuenta_bancaria_id'), false );

  var valor = $('#teso_medio_recaudo_id').val().split('-');
  if ( valor[1]=='Tarjeta bancaria' )
  {
    mostrar_campo_formulario( $('#teso_cuenta_bancaria_id'), '*Cuenta bancaria:', true );
  }else{
    mostrar_campo_formulario( $('#teso_caja_id'), '*Caja:', true );
  }

  $('#teso_medio_recaudo_id').change(function()
  {
    if ( $(this).val() == '' )
    {
      ocultar_campo_formulario( $('#teso_caja_id'), false );
      ocultar_campo_formulario( $('#teso_cuenta_bancaria_id'), false );
      $(this).focus();
      return false;
    }

    var valor = $(this).val().split('-');

    if (valor[1]=='Tarjeta bancaria')
    {
      ocultar_campo_formulario( $('#teso_caja_id'), false );
      mostrar_campo_formulario( $('#teso_cuenta_bancaria_id'), '*Cuenta bancaria:', true );
    }else{
      ocultar_campo_formulario( $('#teso_cuenta_bancaria_id'), false );
      mostrar_campo_formulario( $('#teso_caja_id'), '*Caja:', true );
    }
  });

	CKEDITOR.replace('documento_soporte', {
		toolbar: [{
          name: 'clipboard',
          items: ['PasteFromWord', '-', 'Undo', 'Redo']
        },
        {
          name: 'basicstyles',
          items: ['Bold', 'Italic', 'Underline', 'Strike', 'RemoveFormat', 'Subscript', 'Superscript']
        },
        {
          name: 'links',
          items: ['Link', 'Unlink']
        },
        {
          name: 'paragraph',
          items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote']
        },
        {
          name: 'insert',
          items: ['Image', 'Table']
        },
        {
          name: 'editing',
          items: ['Scayt']
        },
        '/',

        {
          name: 'styles',
          items: ['Format', 'Font', 'FontSize']
        },
        {
          name: 'colors',
          items: ['TextColor', 'BGColor', 'CopyFormatting']
        },
        {
          name: 'align',
          items: ['JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock']
        },
        {
          name: 'document',
          items: ['Print', 'Source']
        }
      ],

      // Enabling extra plugins, available in the full-all preset: https://ckeditor.com/cke4/presets
      extraPlugins: 'colorbutton,font,justify,print,tableresize,pastefromword,liststyle',
      removeButtons: '',
	  height: 200
	});
  
});



function ejecutar_acciones_con_item_sugerencia( item_sugerencia, obj_text_input )
{
  $('#core_tercero_id').before('<input type="hidden" id="vehiculo_id" name="vehiculo_id" value="' + item_sugerencia.attr('data-vehiculo_id') + '" >');
}