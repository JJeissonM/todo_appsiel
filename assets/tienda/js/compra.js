const listaProductos = document.querySelector('#lista-productos tbody');
document.addEventListener('DOMContentLoaded', leerLocalStorage);
function leerLocalStorage() {

    let productosLS;

    productosLS = obtenerProductosLocalStorage();

    productosLS.forEach(function(producto){

        // constrir el template
        const row = document.createElement('tr');
        row.innerHTML = `
             <td>  
                 <center><img src="${producto.imagen}" width=100></center> 
             </td>
             <td><center>${producto.titulo}</center></td>
             <td width="150px"><center>${producto.precio}</center></td>
             <td width="150px" class="accion"><center><a style="color:red" href="" onclick="down(event,${producto.id})"><i class="fa fa-minus-square-o" aria-hidden="true"></i></a><p style="margin: 0;">${producto.cantidad}</p><a href="" style="color: #00cc66" onclick="up(event,${producto.id})"><i class="fa fa-plus" aria-hidden="true"></i></a></center></td>
             <td width="150px"><center>${parseFloat(producto.precio/ ( 1 + producto.tasa_impuesto/100)).toFixed(2)}%</center></td>   
             <td width="150px"><center>${parseFloat(producto.total*(producto.tasa_impuesto/100))}</center></td>       
             <td width="150px" class="total"><center>${producto.total+producto.total*(producto.tasa_impuesto/100)}</center></td>         
             <td>
                  <center><a style="color: red" href="#" onclick="eliminar(event,${producto.id})" class="borrar-curso" data-id="${producto.id}"><i class="fa fa-times-circle" aria-hidden="true"></i></a></center>
             </td>
        `;

        listaProductos.appendChild(row);
        total();
    });
}
// Comprueba que haya elementos en Local Storage
function obtenerProductosLocalStorage() {

    let productosLS;

    // comprobamos si hay algo en localStorage
    if(localStorage.getItem('productos') === null) {
        productosLS = [];
    } else {
        productosLS = JSON.parse( localStorage.getItem('productos') );
    }

    return productosLS;
}
function total(){
    let productosLS,total = 0,subtotal = 0;
    productosLS = obtenerProductosLocalStorage();
    productosLS.forEach((value,index)=>{
        subtotal += value.total;
        total +=value.total+ value.total*(value.tasa_impuesto/100);
    });

    document.getElementById('subtotal').innerHTML = `$${subtotal}`;
    document.getElementById('iva').innerHTML = `$${parseFloat(total-subtotal).toFixed(2)}`;
    document.getElementById('total').innerHTML = `$${total.toFixed(2)}`;
}
function down(event,id){
   event.preventDefault();
   let parent = event.target.parentElement.parentElement;
   let p = parent.querySelector('p');
   let total = parent.parentElement.parentElement.querySelector('.total');
   let productos = obtenerProductosLocalStorage();
   let array = [];

   productos.forEach((value,index) => {
      if(value.id == id) {

          value.cantidad = value.cantidad - 1;
          if(value.cantidad == 0){
             value.cantidad = 1;
          }
          value.total = value.precio*value.cantidad;
          total.innerHTML = `<center>${value.total}</center>`;
          p.innerText = value.cantidad;
      }
      array.push(value);
   });

    // Añadimos el arreglo actual a storage
    localStorage.setItem('productos', JSON.stringify(array));
    let productosLS,total_venta = 0,subtotal = 0;
    productosLS = array;
    productosLS.forEach((value,index)=>{
        subtotal = value.total;
        total_venta += value.total*value.total*(value.tasa_impuesto/100);
    });

    document.getElementById('subtotal').innerHTML = `$${subtotal}`;
    document.getElementById('iva').innerHTML = `$${parseFloat(total_venta-subtotal).toFixed(2)}`;
    document.getElementById('total').innerHTML = `$${total_venta.toFixed(2)}`;
}
function up (event,id){

    event.preventDefault();
    let parent = event.target.parentElement.parentElement;
    let p = parent.querySelector('p');
    let total = parent.parentElement.parentElement.querySelector('.total');
    let productos = obtenerProductosLocalStorage();
    let array = [];
    productos.forEach((value,index) => {
        if(value.id == id) {
            value.cantidad = value.cantidad + 1;
            p.innerText = value.cantidad;
            value.total = value.precio*value.cantidad;
            total.innerHTML = `<center>${value.total}</center>`;
        }
        array.push(value);
    });
    // Añadimos el arreglo actual a storage
    let productosLS,total_venta = 0,subtotal = 0;
    productosLS = array;
    productosLS.forEach((value,index)=>{
        subtotal = value.total;
        total_venta += value.total*value.total*(value.tasa_impuesto/100);
    });

    document.getElementById('subtotal').innerHTML = `$${subtotal}`;
    document.getElementById('iva').innerHTML = `$${parseFloat(total_venta-subtotal).toFixed(2)}`;
    document.getElementById('total').innerHTML = `$${total_venta.toFixed(2)}`;
}
function eliminar(event,id) {

    event.preventDefault();
    let parent = event.target.parentElement.parentElement.parentElement.parentElement;
    parent.parentNode.removeChild(parent);

    let productos = obtenerProductosLocalStorage();
    productos.forEach((value,index) => {
        if(value.id == id) {
            productos.splice(index,1);
        }
    });

    // Añadimos el arreglo actual a storage
    localStorage.setItem('productos', JSON.stringify(productos));
    total();
}

