
<div style="text-align: center; width: 100%; background: #ddd; font-weight: bold;">Empleados de la Orden de Trabajo</div>
@include('nomina.ordenes_de_trabajo.show_empleados')


<div style="text-align: center; width: 100%; background: #ddd; font-weight: bold;">Items de la Orden de Trabajo</div>
@include('nomina.ordenes_de_trabajo.show_items')



@include('components.design.ventana_modal',['titulo'=>'Editar registro','texto_mensaje'=>''])