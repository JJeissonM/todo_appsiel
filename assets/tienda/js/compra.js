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
             <td width="150px" class="total"><center>${parseFloat(producto.precio.substring(1,producto.precio.length))*parseFloat(producto.cantidad)}</center></td>         
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
function down(event,id){

   event.preventDefault();
   let parent = event.target.parentElement.parentElement;
   let p = parent.querySelector('p');
   let total = parent.parentElement.parentElement.querySelector('.total');
   let productos = obtenerProductosLocalStorage();

   productos.forEach((value,index) => {

      if(value.id == id) {

          value.cantidad = value.cantidad - 1;

          if(value.cantidad == 0){
              productos.splice(index,1);
              element = parent.parentElement.parentElement;
              parent = element.parentNode.removeChild(element);
          }
          value.total = parseFloat(value.precio.substring(1,value.precio.length))*parseFloat(value.cantidad);
          total.innerHTML = `<center>${value.total}</center>`;
          p.innerText = value.cantidad;
      }
   });

    // Añadimos el arreglo actual a storage
    localStorage.setItem('productos', JSON.stringify(productos));
    total();
}
function up (event,id){

    event.preventDefault();
    let parent = event.target.parentElement.parentElement;
    let p = parent.querySelector('p');
    let total = parent.parentElement.parentElement.querySelector('.total');
    let productos = obtenerProductosLocalStorage();

    productos.forEach((value,index) => {

        if(value.id == id) {

            value.cantidad = value.cantidad + 1;
            p.innerText = value.cantidad;
            value.total = parseFloat(value.precio.substring(1,value.precio.length))*parseFloat(value.cantidad);
            total.innerHTML = `<center>${value.total}</center>`;
        }
    });

    // Añadimos el arreglo actual a storage
    localStorage.setItem('productos', JSON.stringify(productos));
    total();
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
function total(){

    let productosLS,total = 0;
    productosLS = obtenerProductosLocalStorage();
    productosLS.forEach((value,index)=>{
        total += parseFloat(value.precio.substring(1,value.precio.length))*parseFloat(value.cantidad);
    });

    document.getElementById('subtotal').innerHTML = `$${total}`;
    document.getElementById('total').innerHTML = `$${total}`;
}
