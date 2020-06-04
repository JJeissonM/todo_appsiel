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
             <td width="150px"><center>${producto.cantidad}</center></td>         
             <td width="150px"><center>${parseFloat(producto.precio.substring(1,producto.precio.length))*parseFloat(producto.cantidad)}</center></td>         
             <td>
                  <center><a href="#" class="borrar-curso" data-id="${producto.id}">X</a></center>
             </td>
        `;

        listaProductos.appendChild(row);
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