const megamenu = document.getElementsByClassName('megamenu-title');
const pt_vmegamenu = document.getElementById('pt_vmegamenu');
const menu_categories =  document.getElementById('navbar-inner');
const ma_mobilemenu = document.getElementById('ma-mobilemenu');

megamenu[0].addEventListener('click',function(event){
    pt_vmegamenu.classList.toggle('active_megamenu');
}); 

menu_categories.addEventListener('click',function(event){
    ma_mobilemenu.classList.toggle('active_megamenu');
});