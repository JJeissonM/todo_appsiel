/// StickNav - Al desplazar scroll
/*var stickyNavTop = $('.nav').offset().top;

var stickyNav = function(){
  var scrollTop = $(window).scrollTop();

  if (scrollTop > stickyNavTop) {
    $('.logo').attr('width','32');
    $('.navbar-collapse').addClass('menu-top');
    $('.navbar-collapse').removeClass('menu');
    $('.navbar').attr('style','background-color:#FFF; box-shadow: 0 0 5px 2px rgba(0,0,0,.33);');
    
  } else {
    $('.logo').attr('width','150');
    $('.navbar-collapse').addClass('menu');
    $('.navbar-collapse').removeClass('menu-top');
    $('.navbar').attr('style','background-color:transparent; border:0;');
  }
};
stickyNav();
$(window).scroll(function() {
  stickyNav();
});

$(".logo").mouseover(function(){
    $(this).css("opacity", "0.3");
  });
$(".logo").mouseout(function(){
    $(this).css("opacity", "1");
  });
  */

// Slide (Secciones)
$(window).scroll( function() {
  $(".slideanim").each(function(){
    var pos = $(this).offset().top;

    var winTop = $(window).scrollTop();
      if (pos < winTop + 600) {
        $(this).addClass("slide");
      }
  });

} );


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