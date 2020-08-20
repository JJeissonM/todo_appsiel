<style>

    #slider {
         margin: 0;
         padding: 0;
    }

     #slider img {
         object-fit: cover;
         width: 100%;
         padding: 0;
    }
</style>

<div id="slider" class="container-fluid">

    <img class="image" src="{{asset($slider->items->first()->imagen)}}" alt="">

</div>



