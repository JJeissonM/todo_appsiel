<div class="col-sm-12">
    <div class="section-header">
        <h2 class="section-title text-center wow fadeInDown animated"
            style="visibility: visible; animation-name: fadeInDown;">PREGUNTAS FRECUENTES</h2>
    </div>
    <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
        @foreach($preguntas as $item)
            <div class="panel panel-collapse col-md-12">
                <div class="panel-heading" role="tab" id="heading{{$item->id}}">
                    <h4 class="panel-title">
                        <button class="col-md-12 collapsed"
                                style="padding: 15px; cursor: pointer;text-align: left;border-radius: 5px; border: 1px solid; border-color: #1b6d85;"
                                data-toggle="collapse" data-parent="#accordion" href="#collapse{{$item->id}}"
                                aria-expanded="false" aria-controls="collapse{{$item->id}}">
                            {{$item->pregunta}}<i class="fa fa-plus" style="margin-right: 0px; float: right;"></i>
                        </button>
                    </h4>
                </div>
                <div id="collapse{{$item->id}}" class="panel-collapse collapse" role="tabpanel"
                     aria-labelledby="heading{{$item->id}}" aria-expanded="false" style="height: 0px;">
                    <div class="panel-body">
                        {{$item->respuesta}}
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>