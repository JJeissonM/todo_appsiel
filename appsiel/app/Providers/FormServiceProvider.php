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
        Form::component('multiselect_autocomplete', 'components.form.multiselect_autocomplete', ['name', 'value', 'lbl', 'opciones', 'attributes']);
        
        Form::component('bsInputListaSugerencias', 'components.form.input_lista_sugerencias', ['name', 'value', 'lbl', 'attributes']);

        Form::component('bsCheckBox', 'components.form.checkbox', ['name', 'value', 'lbl', 'opciones', 'attributes']);
        Form::component('bsRadioBtn', 'components.form.radio', ['name', 'value', 'lbl', 'opciones', 'attributes']);

        Form::component('bsFecha', 'components.form.fecha', ['name', 'value', 'lbl', 'opciones', 'attributes']);
        Form::component('bsHora', 'components.form.hora', ['name', 'value', 'lbl', 'opciones', 'attributes']);

        Form::component('bsHidden', 'components.form.hidden', ['name', 'value']);

        Form::component('bsButtonsForm', 'components.form.buttons_form', ['url_cancelar']);
        Form::component('bsButtonsForm2', 'components.form.buttons_form2', ['url_cancelar']);
        Form::component('bsBtnEdit', 'components.form.btn_edit', ['url']);
        Form::component('bsBtnEliminar', 'components.form.btn_eliminar', ['url']);
        Form::component('bsBtnEdit2', 'components.form.btn_edit2', ['url', 'etiqueta']);
        Form::component('bsBtnCreate', 'components.form.btn_create', ['url','target'=>'_self']);
        Form::component('bsBtnVolver', 'components.form.btn_volver', ['url']);
        Form::component('bsBtnPrint', 'components.form.btn_print', ['url']);
        Form::component('bsBtnEmail', 'components.form.btn_email', ['url']);
        Form::component('bsBtnVer', 'components.form.btn_ver', ['url']);
        Form::component('bsBtnPrev', 'components.form.btn_prev', ['url']);
        Form::component('bsBtnNext', 'components.form.btn_next', ['url']);
        Form::component('bsBtnEstado', 'components.form.btn_estado', ['url']);
        Form::component('bsBtnExcel', 'components.form.btn_excel', ['nombre_listado']);
        Form::component('bsBtnExcelV2', 'components.form.btn_excel_v2', ['nombre_listado']);
        Form::component('bsBtnPdf', 'components.form.btn_pdf', ['nombre_listado']);
        Form::component('bsBtnDropdown', 'components.form.btn_dropdown', ['etiqueta', 'clase', 'icono', 'urls']);

        Form::component('bsBtnPrevNext', 'components.form.btn_prev_next', ['etiqueta', 'clase', 'icono', 'urls']);

        Form::component('formEliminar', 'components.form.form_eliminar', ['url', 'recurso_id']);

        Form::component('bsTableHeader', 'components.design.table_header', ['headers']);
        Form::component('bsMigaPan', 'components.design.miga_pan', ['vec']);

        Form::component('NombreMes', 'components.design.nombre_mes', ['mes']);
        Form::component('TextoMoneda', 'components.design.texto_moneda', ['valor', 'lbl' => null]);
        Form::component('Spin', 'components.design.spin', ['tamaño']);
        Form::component('Spin2', 'components.design.spin2', ['tamaño']);
        Form::component('btnInfo', 'components.design.btn_info', ['title']);

        //Form::component('HrefDocEncabezado', 'components.design.btn_info', ['title']);

        self::webComponent();
    }



    public function webComponent()
    {
        //componentes utilizado en el diseñador de la pagina web
        Form::component('navegacion', 'components.web.navegacion', ['nav']);
        Form::component('navegacionpremium', 'components.web.navegacionpremium', ['nav']);
        Form::component('slider', 'components.web.slider', ['slider']);
        Form::component('sliderpremiun', 'components.web.sliderpremiun', ['slider']);
        Form::component('sliderbootstrap', 'components.web.sliderbootstrap', ['slider']);
        Form::component('sliderappsiel', 'components.web.sliderappsiel', ['slider']);
        Form::component('aboutus', 'components.web.aboutus', ['aboutus']);
        Form::component('aboutuspremiun', 'components.web.aboutuspremiun', ['aboutus']);
        Form::component('galeria', 'components.web.galeria2', ['galeria']);
        Form::component('articles', 'components.web.articles', ['articles', 'setup']);
        Form::component('servicios', 'components.web.servicios', ['servicios']);
        Form::component('iconos', 'components.web.iconos', ['iconos']);
        Form::component('contactenos', 'components.web.contactenos', ['contactenos']);
        Form::component('clientes', 'components.web.clientes', ['clientes']);
        Form::component('archivos', 'components.web.archivos', ['items', 'archivo']);
        Form::component('footer', 'components.web.footer', ['footer', 'redes', 'contactenos', 'view']);
        Form::component('preguntas', 'components.web.preguntas', ['pregunta']);
        Form::component('tienda', 'components.web.tienda.tienda', ['items', 'grupos','pedido']);
        Form::component('navegaciontienda', 'components.web.tienda.navegaciontienda', ['items', 'grupos']);
        Form::component('categoriestienda', 'components.web.tienda.categoriestienda', ['items', 'grupos']);
        Form::component('testimoniales', 'components.web.testimoniales', ['testimonial']);
        Form::component('custom_html', 'components.web.custom_html', ['registro']);
        Form::component('pqr', 'components.web.pqr', ['registro', 'pagina']);
        Form::component('parallax', 'components.web.parallax', ['parallax']);
        Form::component('sticky', 'components.web.sticky', ['sticky']);
        Form::component('modal', 'components.web.modal', ['modal']);
        Form::component('guias_academicas', 'components.web.guias_academicas', ['cursos']);
        Form::component('login', 'components.web.login', ['login']);
        Form::component('team', 'components.web.team', ['team']);
        Form::component('Price', 'components.web.price', ['Price']);
        Form::component('formcontacto', 'components.web.contactenos2', ['contactenos']);
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
