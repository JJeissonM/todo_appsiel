const fs=require('fs'),vm=require('vm'),assert=require('assert');
const values={'#pos_ocultar_cantidad':'1','#quantity':'7','#forma_lectura_codigo_barras':'codigo_cantidad'};
let focus='',added=[];
const document={};
function $(selector){return {val(v){if(arguments.length){values[selector]=v;return this;}return values[selector];},ready(){},html(){return this;},show(){return this;},hide(){return this;},css(){return this;},text(){return this;},modal(){return this;},focus(){focus=selector;return this;},select(){focus=selector;return this;}};}
$.isNumeric=v=>!isNaN(parseFloat(v));
const product={id:1,tasa_impuesto:19,unidad_medida1:'UND',costo_promedio:50,descripcion:'Producto',existencia_actual:100};
const c={$,document,console,productos:[product],get_precio:()=>119,get_descuento:()=>10,
calcular_valor_descuento(){c.precio_unitario=Number(values['#precio_unitario']);c.valor_unitario_descuento=c.precio_unitario*Number(values['#tasa_descuento'])/100;},
calcular_impuestos(){},mostrar_mensaje_item_agregado(){},Swal:{fire(){throw Error('Validación inesperada');}}};
vm.createContext(c);
function include(file,name){const src=fs.readFileSync(file,'utf8'),start=src.indexOf('function '+name+'(');assert(start>=0,name);let end=src.indexOf('\nfunction ',start+1);vm.runInContext(src.slice(start,end<0?src.length:end),c);}
vm.runInContext(fs.readFileSync('assets/js/ventas_pos/cantidad_unitaria.js','utf8'),c);
// Cálculo real de precios y rutas reales de selección.
include('assets/js/ventas_pos/precios.js','calcular_precio_total');
['mandar_codigo','mandar_codigo2','mandar_codigo3','mandar_codigo4'].forEach(n=>include('assets/js/ventas_pos/agregar_linea_item.js',n));
['agregar_linea_producto_ingresado','get_quantity_from_barcode'].forEach(n=>include('assets/js/ventas_pos/commons.js',n));
c.agregar_la_linea=()=>{added.push({cantidad:Number(c.cantidad),total:c.precio_total});return true;};
c.agregar_nueva_linea=()=>{c.calcular_precio_total();c.agregar_la_linea();};
for(const enabled of ['1','0']){
 values['#pos_ocultar_cantidad']=enabled;
 for(const name of ['mandar_codigo','mandar_codigo2','mandar_codigo4']){
  values['#quantity']='7';c.cantidad=99;c[name](1);
  const expected=name==='mandar_codigo4' && enabled==='0'?7:1;
  assert.strictEqual(added[added.length-1].cantidad,expected,name+enabled);
  assert(Math.abs(added[added.length-1].total-107.1*expected)<0.00001);
 }
 c.mandar_codigo3(1);assert.strictEqual(focus,enabled==='1'?'#precio_unitario':'#cantidad');
 for(const method of ['id','referencia']){
  c.agregar_linea_producto_ingresado(product,'', '',method);
  assert.strictEqual(focus,enabled==='1'?'#precio_unitario':'#cantidad');
 }
 c.agregar_linea_producto_ingresado(product,'01234502500','119','barcode');
 assert.strictEqual(added[added.length-1].cantidad,enabled==='1'?1:2.5);
}
console.log('OK: manual, ID/referencia, lupa, táctil, cinta y código de peso; descuento e IVA con configuración activa/inactiva.');
