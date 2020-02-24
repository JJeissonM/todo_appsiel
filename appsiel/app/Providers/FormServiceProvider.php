<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Form;

class FormServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // Register the form components
        Form::component('bsLabel', 'components.form.label', ['name', 'value', 'lbl', 'attributes']);
        Form::component('bsText', 'components.form.text', ['name', 'value', 'lbl', 'attributes']);
        Form::component('bsEmail', 'components.form.email', ['name', 'value', 'lbl', 'attributes']);
        Form::component('bsNumber', 'components.form.number', ['name', 'value', 'lbl', 'attributes']);
        Form::component('bsTextArea', 'components.form.text_area', ['name', 'value', 'lbl', 'attributes']);
		Form::component('bsPassword', 'components.form.password', ['name', 'value', 'lbl', 'attributes']);
		
        
        Form::component('bsSelect', 'components.form.select', ['name', 'value', 'lbl', 'opciones', 'attributes']);
        Form::component('bsSelectCreate', 'components.form.select_create', ['name', 'value', 'lbl', 'opciones', 'attributes']);
        Form::component('bsSelectName', 'components.form.select_name', ['name', 'value', 'lbl', 'opciones', 'attributes']);
        Form::component('bsSelectTerceros', 'components.form.select_terceros', ['name', 'value', 'lbl', 'opciones', 'attributes']);

        Form::component('bsCheckBox', 'components.form.checkbox', ['name', 'value', 'lbl', 'opciones', 'attributes']);
        Form::component('bsRadioBtn', 'components.form.radio', ['name', 'value', 'lbl', 'opciones', 'attributes']);

        Form::component('bsFecha', 'components.form.fecha', ['name', 'value', 'lbl', 'opciones', 'attributes']);
        Form::component('bsHora', 'components.form.hora', ['name', 'value', 'lbl', 'opciones', 'attributes']);

        Form::component('bsButtonsForm', 'components.form.buttons_form', ['url_cancelar']);
        Form::component('bsButtonsForm2', 'components.form.buttons_form2', ['url_cancelar']);
        Form::component('bsBtnEdit', 'components.form.btn_edit', ['url']);
        Form::component('bsBtnEliminar', 'components.form.btn_eliminar', ['url']);
        Form::component('bsBtnEdit2', 'components.form.btn_edit2', ['url','etiqueta']);
        Form::component('bsBtnCreate', 'components.form.btn_create', ['url']);
        Form::component('bsBtnVolver', 'components.form.btn_volver', ['url']);
        Form::component('bsBtnPrint', 'components.form.btn_print', ['url']);
        Form::component('bsBtnEmail', 'components.form.btn_email', ['url']);
        Form::component('bsBtnVer', 'components.form.btn_ver', ['url']);
        Form::component('bsBtnPrev', 'components.form.btn_prev', ['url']);
        Form::component('bsBtnNext', 'components.form.btn_next', ['url']);
        Form::component('bsBtnEstado', 'components.form.btn_estado', ['url']);
        Form::component('bsBtnExcel', 'components.form.btn_excel', ['nombre_listado']);
        Form::component('bsBtnPdf', 'components.form.btn_pdf', ['nombre_listado']);
        Form::component('bsBtnDropdown', 'components.form.btn_dropdown', ['etiqueta','clase','icono','urls']);

        Form::component('bsBtnPrevNext', 'components.form.btn_prev_next', ['etiqueta','clase','icono','urls']);

        Form::component('formEliminar', 'components.form.form_eliminar', ['url','recurso_id']);

        Form::component('bsTableHeader', 'components.design.table_header', ['headers']);
        Form::component('bsMigaPan', 'components.design.miga_pan', ['vec']);

        Form::component('NombreMes', 'components.design.nombre_mes', ['mes']);
        Form::component('TextoMoneda', 'components.design.texto_moneda', ['valor']);
        Form::component('Spin', 'components.design.spin', ['tamaño']);
        Form::component('btnInfo', 'components.design.btn_info', ['title']);

        //Form::component('HrefDocEncabezado', 'components.design.btn_info', ['title']);

        self::webComponent();

    }



    public function webComponent(){
        //componentes utilizado en el diseñador de la pagina web
        Form::component('navegacion','components.web.navegacion',['nav']);
        Form::component('slider','components.web.slider',['slider']);
        Form::component('aboutus','components.web.aboutus',['aboutus']);
        Form::component('galeria','components.web.galeria',['galeria']);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
