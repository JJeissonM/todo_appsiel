@if($modal != null)
<style>

</style>

<div id="modal">
    <!-- Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content" style="position: relative;">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="position: absolute; top: 2px; right: 8px; background-color: red; border-radius: 50%; color: white; padding: 3px; z-index: 1000; cursor: pointer;">
                    <span aria-hidden="true">&times;</span>
                </button>
                <div class="modal-body">
                        <div class="d-flex items-center justify-content-center">
                            <div class="card" style="width: 100%;">
                                <img loading="lazy" class="card-img-top" src="{{asset($modal->path != ''?$modal->path : 'assets/img/learning_background.jpg')}}" alt="Card image cap" style="height: 250px">
                                <div class="card-body" style="margin: 10px;">
                                    <div style="margin-top: 10px; font-weight: bold;">{{$modal->title}}</div>
                                    <p class="card-text">{{$modal->body}}</p>
                                    <a href="{{$modal->enlace}}" class="btn btn-info">Call to Action</a>
                                </div>
                            </div>
                        </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
   window.onload = () => {
       setTimeout(()=>{
            $('#exampleModal').modal('show');
       },30);
   };
</script>

@endif