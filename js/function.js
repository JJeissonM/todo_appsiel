function comprueba_extension(formulario, archivo) {
   extensiones_permitidas = new Array(".xls", ".xlsx", ".xslm", ".xltx", ".xml");
   mierror = "";
   if (!archivo) {
      //Si no tengo archivo, es que no se ha seleccionado un archivo en el formulario
       mierror = "No has seleccionado ningún archivo";
       alert (mierror);
       window.location='/importar_excel';
   }else{
      //recupero la extensión de este nombre de archivo
      extension = (archivo.substring(archivo.lastIndexOf("."))).toLowerCase();
      //alert (extension);
      //compruebo si la extensión está entre las permitidas
      permitida = false;
      for (var i = 0; i < extensiones_permitidas.length; i++) {
         if (extensiones_permitidas[i] == extension) {
         permitida = true;
         break;
         }
      }
      if (!permitida) {
         mierror = "Comprueba la extensión del archivo. \nSólo se pueden subir archivos con extensiones: " + extensiones_permitidas.join();
         alert (mierror);
         window.location='/importar_excel';
         
       }else{
         //alert ("Todo correcto.");
         formulario.submit();
         return 1;
       }
   }
   //si estoy aqui es que no se ha podido submitir 
   alert (mierror);
   return 0;
} 

$(function(){                
             var datos =
             {
                "metadata":[
                {  "name":"id_colegio",       "label":"id_colegio","numeric":"string","editable":true},
                {  "name":"nombres",       "label":"nombres","string":"string","editable":true},
                {  "name":"apellido1",       "label":"apellido1","string":"string","editable":true},
                {  "name":"apellido2",       "label":"apellido2","string":"string","editable":true},
                {  "name":"tipo_doc_id",       "label":"tipo_doc_id","numeric":"string","editable":true},
                {  "name":"doc_identidad",       "label":"doc_identidad","string":"string","editable":true},
                {  "name":"genero",       "label":"genero","string":"string","editable":true},
                {  "name":"direccion",       "label":"direccion","string":"string","editable":true},
                {  "name":"barrio",       "label":"barrio","string":"string","editable":true},
                {  "name":"telefono",       "label":"telefono","string":"string","editable":true},
                {  "name":"fecha_nacimiento",       "label":"fecha_nacimiento","":"string","editable":true},
                {  "name":"ciudad_nacimiento",       "label":"ciudad_nacimiento","string":"string","editable":true},
                {  "name":"mama",       "label":"mama","string":"string","editable":true},
                {  "name":"ocupacion_mama",       "label":"ocupacion_mama","string":"string","editable":true},
                {  "name":"telefono_mama",       "label":"telefono_mama","string":"string","editable":true},
                {  "name":"email_mama",       "label":"email_mama","string":"string","editable":true},
                {  "name":"papa",       "label":"papa","string":"string","editable":true},
                {  "name":"ocupacion_papa",       "label":"ocupacion_papa","string":"string","editable":true},
                {  "name":"telefono_papa",       "label":"telefono_papa","string":"string","editable":true},
                {  "name":"email_papa",       "label":"email_papa","string":"string","editable":true}
                ],

                 "data" : data
            };
            //console.log(datos);

            //editableGrid = new EditableGrid("import-excel");
            //editableGrid.load(datos);
            //editableGrid.renderGrid("tablecontent", "table table-hover", "import-excel");
           
});


$(document).ready(function () {
            
     $.ajax({
        url: "validate-albaran",
        type: "get",
        dataType: "json",
        data:  $("input[name='albaran[]']"),

    }).done(function(response){
        console.log(response);
    });
    $('[data-toggle="popover"]').popover(); 
});