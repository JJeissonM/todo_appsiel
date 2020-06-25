// Variables
const carrito = document.getElementById('carrito');
const productos = document.getElementById('lista_productos');
const listaProductos = document.querySelector('#lista-carrito tbody');
const vaciarCarritoBtn = document.getElementById('vaciar-carrito'); 

// Listeners
cargarEventListeners();

function cargarEventListeners() {

     // Dispara cuando se presiona "Agregar Carrito"
     productos.addEventListener('click', comprarProducto);

     // Cuando se elimina un curso del carrito
     carrito.addEventListener('click', eliminarProducto);

     // Al Vaciar el carrito
     vaciarCarritoBtn.addEventListener('click', vaciarCarrito);

     // Al cargar el documento, mostrar LocalStorage
    document.addEventListener('DOMContentLoaded', leerLocalStorage);

}

// Funciones
// Función que añade el curso al carrito
function comprarProducto(e) {

     e.preventDefault();
     // Delegation para agregar-carrito
     if(e.target.parentElement.classList.contains('agregar-carrito')) {
         //e.target.style.backgroundColor = 'rgb(249, 123, 0)';
          let producto = e.target.parentElement.parentElement;
          // Enviamos el curso seleccionado para tomar sus datos
          toastr.success(`${producto.querySelector('.product-name a').textContent} agregado al carrito`)
          leerDatosProducto(producto);
     }

}
// Lee los datos del curso
function leerDatosProducto(producto) {

     const infoProducto = {
          imagen: producto.querySelector('.product-image img').src,
          titulo: producto.querySelector('.product-name a').textContent,
          precio: producto.querySelector('#precio_venta').value,
          cantidad: 1,
          tasa_impuesto: producto.querySelector('#tasa_impuesto').value,
          total:parseFloat(producto.querySelector('#precio_venta').value),
          id: producto.getAttribute('data-id')
     }

     insertarCarrito(infoProducto);
}
// Muestra el curso seleccionado en el Carrito
function insertarCarrito(producto) {

    listaProductos.innerHTML = '';
    guardarProductoLocalStorage(producto);
    let productos = obtenerProductosLocalStorage();
    productos.forEach(item => {

        const row = document.createElement('tr');

        row.innerHTML = `
          <td>  
               <img src="${item.imagen}" width=100>
          </td>
          <td>${item.titulo}</td>
          <td>${item.precio}</td>
          <td>${item.cantidad}</td>
          <td>
               <a href="#" class="borrar-curso" data-id="${item.id}">X</a>
          </td>
        `;

        listaProductos.appendChild(row);

    });

    let element  =  document.querySelector('.item-nav .item');
    let count = productos.length;
    element.style.display = 'block';
    element.innerHTML = `${count}`;


}
// Elimina el curso del carrito en el DOM
function eliminarProducto(e) {

     e.preventDefault();

     let producto,
        productoId;
     if(e.target.classList.contains('borrar-curso') ) {
          e.target.parentElement.parentElement.remove();
          producto = e.target.parentElement.parentElement;
          productoId = producto.querySelector('a').getAttribute('data-id');
     }
     eliminarProductoLocalStorage(productoId);
    let element  =  document.querySelector('.item-nav .item');
    let count = element.textContent;
    if( count != 0 ){
        count = parseInt(count)-1;
        element.style.display = count == 0 ? 'none':'block';
        element.innerHTML = `${count}`;
    }else{
        element.style.display = 'none';
        element.innerHTML = `${count}`;
    }

}
// Elimina los cursos del carrito en el DOM
function vaciarCarrito() {
     // forma lenta
     // listaCursos.innerHTML = '';
     // forma rapida (recomendada)
     while(listaProductos.firstChild) {
         listaProductos.removeChild(listaProductos.firstChild);
     }

    let element  =  document.querySelector('.item-nav .item');
    let count = element.textContent;
    count = 0;
    element.style.display = count == 0 ? 'none' : 'block';
    element.innerHTML = `${count}`;

     // Vaciar Local Storage
     vaciarLocalStorage();

     return false;
}
// Almacena cursos en el carrito a Local Storage
function guardarProductoLocalStorage(producto) {

    let productos = [];
     // Toma el valor de un arreglo con datos de LS o vacio
    productos = obtenerProductosLocalStorage();
    let exist = false;

    productos.forEach(item => {
        if(item.id == producto.id){
            item.cantidad++;
            item.total = item.precio*item.cantidad;
            exist = true;
        }
    });

    // el curso seleccionado se agrega al arreglo
    if(!exist){
        productos.push(producto);
    }

    localStorage.setItem('productos', JSON.stringify(productos));
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

     if(productosLS.length > 0){
         let count  =  document.querySelector('.item-nav .item');
         count.style.display = 'block';
         count.innerHTML = `${productosLS.length}`;
     }else {
         let count  =  document.querySelector('.item-nav .item');
         count.style.display = 'none';
     }

     return productosLS;

}

// Imprime los productos de Local Storage en el carrito

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
             <td><center>${producto.precio}</center></td>
             <td><center>${producto.cantidad}</center></td>         
             <td>
                  <center><a href="#" class="borrar-curso" data-id="${producto.id}">X</a></center>
             </td>
        `;

        listaProductos.appendChild(row);

    });
}
// Elimina el producto por el ID en Local Storage

function eliminarProductoLocalStorage(producto) {
    let productosLS;
    // Obtenemos el arreglo de cursos
    productosLS = obtenerProductosLocalStorage();
    // Iteramos comparando el ID del curso borrado con los del LS
    productosLS.forEach(function(productoLS, index) {
        if(productoLS.id === producto) {
            productosLS.splice(index, 1);
        }
    });
    // Añadimos el arreglo actual a storage
    localStorage.setItem('productos', JSON.stringify(productosLS) );
}

// Elimina todos los cursos de Local Storage

function vaciarLocalStorage() {
    localStorage.clear();
}