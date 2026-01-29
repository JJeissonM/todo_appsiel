<style>
.selector-calificacion-popup {
    position: absolute;
    background: #fff;
    border: 1px solid #ccc;
    padding: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    z-index: 1200;
    font-size: 12px;
    display: none;
    min-width: 220px;
    border-radius: 3px;
}
.selector-calificacion-popup .selector-grid {
    display: grid;
    grid-template-columns: repeat(6, 40px);
    gap: 4px;
}
.selector-calificacion-popup button {
    background: #f5f5f5;
    border: 1px solid #d8d8d8;
    color: #333;
    border-radius: 3px;
    cursor: pointer;
    font-size: 12px;
    line-height: 1.2;
}
.selector-calificacion-popup button.selector-value:hover {
    background: #50B794;
    color: #fff;
    border-color: #43a07a;
}
.selector-calificacion-popup .selector-clear {
    margin-top: 8px;
    width: 100%;
    background: #fff;
    border-color: #f1c40f;
    color: #b36c00;
}
</style>

{{ Form::hidden('modo_ingreso_calificaciones', $modo_ingreso_calificaciones ?? 'teclado', ['id' => 'modo_ingreso_calificaciones']) }}

<div id="selector_calificacion_popup" class="selector-calificacion-popup"></div>
