const listaProductos = document.querySelector('#lista-productos tbody');
document.addEventListener('DOMContentLoaded', leerLocalStorage);
const comprar = document.getElementById('comprar');

comprar.addEventListener('click',function (event) {
   event.preventDefault();
   const contrato = document.getElementById('contrato');
   if(contrato.checked){
       let productos = obtenerProductosLocalStorage();
      if(productos.length > 0){
          let lineas_registros = [];
          productos.forEach(x => {
              lineas_registros.push({
                  'inv_producto_id' : x.id,
                  'cantidad' : x.cantidad,
                  'precio_total' : x.total
              });
          })
          let form  = document.getElementById('form');
          let data = {
              'lineas_registros' : JSON.stringify(lineas_registros),
              '_token':form.querySelector('#token').value,
              'pedido_web' : true,
          }
          let url =  form.getAttribute('action');
          comprar.style.display  = 'none';
          let loading = document.getElementById('loading');
          loading.style.display = 'block';
          axios.post(url,data)
              .then(resp => {
                  loading.style.display = 'none';
                  comprar.style.display  = 'block';
                  let data = resp.data;
                  if(data.status == 'error' ){
                      toastr.warning(data.mensaje);
                  }else if (data.status == 'ok'){
                      toastr.success(data.mensaje);
                      // Añadimos el arreglo actual a storage
                      localStorage.setItem('productos', JSON.stringify([]));
                      listaProductos.innerHTML = '';
                  }
              }).catch(error => {
               const url =  document.getElementById('url_login').value;
               toastr.error(`Hubo un error en la solicitud, Asegúrese de estar logeado al momento de realizar el pedido <a href='${url}' style="text-decoration:underline;">Iniciar Sesión</a>`,'',{timeOut: 0,extendedTimeOut: 0});
          });
      }else{
          toastr.warning('La lista de productos en el carrito está vacía');
      }
   }else {
       toastr.warning('Debe aceptar los términos y condiciones para proceder con el pedido');
   }
})

function leerLocalStorage() {

    let productosLS;

    productosLS = obtenerProductosLocalStorage();

    productosLS.forEach(function(producto){

        // constrir el template
        const row = document.createElement('tr');
        row.innerHTML = /*html*/`
             <td>  
                 <center><img src="${producto.imagen}" width=100></center> 
             </td>
             <td style="padding-left: 3px">${producto.titulo}</td>
             <td style="white-space: nowrap" class="text-right">$ ${parseFloat(producto.precio).toFixed(2).toLocaleString("es-ES")}</td>
             <td class="accion">
                <a class="label label-danger" style="color: white" href="" onclick="down(event,${producto.id})">
                    <i class="fa fa-minus" aria-hidden="true"></i>
                </a>
                <p style="padding: 0 8px">${producto.cantidad}</p>
                <a class="label label-success" href="" style="color: white" onclick="up(event,${producto.id})">
                    <i class="fa fa-plus" aria-hidden="true"></i>
                </a>
             </td>     
             <td style="white-space: nowrap" class="text-right" id="prod${producto.id}">$ ${parseFloat(producto.total).toFixed(2).toLocaleString("es-ES")}</td>         
             <td>
                  <center><a style="color: red; font-size: 30px" href="#" onclick="eliminar(event,${producto.id})" class="borrar-curso" data-id="${producto.id}"><i class="fa fa-times-circle" aria-hidden="true"></i></a></center>
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
        subtotal += value.total/ ( 1 + value.tasa_impuesto/100);
        total +=value.total;
    });

    document.getElementById('subtotal').innerHTML = `$ ${parseFloat(subtotal).toFixed(2)}`;
    document.getElementById('iva').innerHTML = `$ ${parseFloat(total-subtotal).toFixed(2)}`;
    document.getElementById('total').innerHTML = `$ ${total.toFixed(2)}`;
}
function down(event,id){
   event.preventDefault();
   let parent = event.target.parentElement.parentElement;
   let p = parent.querySelector('p');
   let total = parent.parentElement.parentElement.querySelector(`#prod${id}`);
   let productos = obtenerProductosLocalStorage();
   let array = [];

   productos.forEach((value,index) => {
      if(value.id == id) {

          value.cantidad = value.cantidad - 1;
          if(value.cantidad == 0){
             value.cantidad = 1;
          }
          value.total = value.precio*value.cantidad;
          total.innerHTML = value.total;
          p.innerText = value.cantidad;
      }
   });

    // Añadimos el arreglo actual a storage
    localStorage.setItem('productos', JSON.stringify(productos));
    let productosLS,total_venta = 0,subtotal = 0;
    productosLS = productos;
    productosLS.forEach((value,index)=>{
        subtotal += value.total/(1+value.tasa_impuesto/100);
        total_venta += value.total;
    });

    document.getElementById('subtotal').innerHTML = `$ ${parseFloat(subtotal).toFixed(2)}`;
    document.getElementById('iva').innerHTML = `$ ${parseFloat(total_venta-subtotal).toFixed(2)}`;
    document.getElementById('total').innerHTML = `$ ${total_venta.toFixed(2)}`;
}
function up (event,id){
    event.preventDefault();
    let parent = event.target.parentElement.parentElement;
    let p = parent.querySelector('p');
    let total = parent.parentElement.parentElement.querySelector(`#prod${id}`);
    let productos = obtenerProductosLocalStorage();
    productos.forEach((value,index) => {
        if(value.id == id) {
            value.cantidad = parseInt(value.cantidad) + 1;
            p.innerText = value.cantidad;
            console.log(p);
            value.total = value.precio*value.cantidad;
            total.innerHTML = value.total;
        }
    });
    // Añadimos el arreglo actual a storage
    // Añadimos el arreglo actual a storage
    localStorage.setItem('productos', JSON.stringify(productos));
    let productosLS,total_venta = 0,subtotal = 0;
    productosLS = productos;
    productosLS.forEach((value,index)=>{
        subtotal += value.total/(1+value.tasa_impuesto/100);
        total_venta += value.total;
    });

    document.getElementById('subtotal').innerHTML = `$ ${parseFloat(subtotal).toFixed(2)}`;
    document.getElementById('iva').innerHTML = `$ ${parseFloat(total_venta-subtotal).toFixed(2)}`;
    document.getElementById('total').innerHTML = `$ ${total_venta.toFixed(2)}`;
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

