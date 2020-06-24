$(document).ready(function(){

  $('.imagen_proceso').fadeIn(1000);

  // Add smooth scrolling to all links in navbar + footer link
  $(".navbar a, footer a[href='#myPage']").on('click', function(event) {
    // Make sure this.hash has a value before overriding default behavior
    if (this.hash !== "") {
      // Prevent default anchor click behavior
      event.preventDefault();

      // Store hash
      var hash = this.hash;

      // Using jQuery's animate() method to add smooth page scroll
      // The optional number (900) specifies the number of milliseconds it takes to scroll to the specified area
      $('html, body').animate({
        scrollTop: $(hash).offset().top
      }, 900, function(){
   
        // Add hash (#) to URL when done scrolling (default click behavior)
        window.location.hash = hash;
      });
    } // End if
  });
  
  $(window).scroll(function() {
    $(".slideanim").each(function(){
      var pos = $(this).offset().top;

      var winTop = $(window).scrollTop();
        if (pos < winTop + 600) {
          $(this).addClass("slide");
        }
    });
  });

  // Accordion
  var acc = document.getElementsByClassName("accordion");
  var i;

  for (i = 0; i < acc.length; i++) {
      acc[i].addEventListener("click", function() {
          this.classList.toggle("active");
          var panel = this.nextElementSibling;
          if (panel.style.display === "block") {
              panel.style.display = "none";
          } else {
              panel.style.display = "block";
          }
      });
  }

  // Precios
  $(".seleccionar").change(function(){
    recalculate();
  });

  function recalculate(){
      var sum = 0;

      $("input[type=checkbox]:checked").each(function(){
        sum += parseInt($(this).val());
      });

      //alert(sum);
      var total = new Intl.NumberFormat("de-DE").format(sum);
      $("#total").text("*$" + total + " COP / mes");
      var total_anual = sum * 12;
      total_anual = new Intl.NumberFormat("de-DE").format(total_anual);
      $("#total_anual").text("*$" + total_anual + " COP / Año");
  }

  $("input[type=number]").change(function(){
        var cod_id = $(this).attr('id');
        var id = cod_id.split("-");
        var precio = $("#precio-"+id[1]).val();
        var cantidad = $("#cantidad-"+id[1]).val();

        var total = precio * cantidad;
        $("#sub_total-"+id[1]).val(total); // Se le asigna el valor al checkbox
        $("#lbl_sub_total-"+id[1]).text("$" + new Intl.NumberFormat("de-DE").format(total) + " COP / mes");

        recalculate();
      });

  $("#tab_anual").click(function(e){
    e.stopImmediatePropagation();
    $("#div_costo_total_mensual").hide();
    $("#div_costo_total_anual").show(1000);
  });

  $("#tab_mensual").click(function(e){
    e.stopImmediatePropagation();
    $("#div_costo_total_mensual").show(1000);
    $("#div_costo_total_anual").hide();
  });

})