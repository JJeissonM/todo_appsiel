
<div class="marco_formulario">
    <h5>
        Categorías de productos
        <small>
            <button style="border: 0; background: transparent; display: none;" title="Mostrar" id="btn_mostrar_resumen_operaciones">
                <i class="fa fa-eye"></i>
            </button>
            <button style="border: 0; background: transparent;" title="Ocultar" id="btn_ocultar_resumen_operaciones">
                <i class="fa fa-eye-slash"></i>
            </button>
        </small>
    </h5>
    <div class="container-fluid" id="div_resumen_operaciones">
        {!! $vista_categorias_productos !!}
    </div>
</div>