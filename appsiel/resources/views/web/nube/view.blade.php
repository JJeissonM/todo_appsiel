@extends('layouts.principal')

@section('estilos_1')
    <style type="text/css">
        .nube-toolbar {
            margin: 20px 0;
        }

        .nube-search {
            margin: 30px auto;
            max-width: 680px;
        }

        .nube-search input {
            border: none;
            border-bottom: 1px solid #ddd;
            background-color: transparent;
            box-shadow: none;
            font-size: 16px;
        }

        .nube-search input:focus {
            border-bottom-color: #42a3dc;
            box-shadow: none;
        }

        .nube-file {
            min-height: 190px;
            margin-bottom: 25px;
            text-align: center;
        }

        .nube-file-icon {
            display: inline-block;
            min-height: 70px;
            font-size: 60px;
            line-height: 1;
        }

        .nube-file-name {
            min-height: 42px;
            margin: 10px 0 8px;
            color: #333;
            word-break: break-word;
        }

        .nube-action {
            display: inline-block;
            margin-top: 5px;
            font-size: 13px;
            cursor: pointer;
        }

        .nube-empty {
            margin: 40px 0;
            color: #777;
            text-align: center;
        }
    </style>
@endsection

@section('content')
    @php
        $id = \Illuminate\Support\Facades\Input::get('id');
    @endphp

    {{ Form::bsMigaPan($miga_pan) }}

    @include('web.templates.messages')

    <div class="row">
        <div class="col-sm-12">
            <div class="page-header">
                <h3>
                    <i class="fa fa-cloud"></i>
                    Almacenamiento en la Nube
                    <small>{{ $path }}</small>
                </h3>
            </div>
        </div>
    </div>

    <div class="row nube-toolbar">
        <div class="col-sm-12">
            @if($prev != 'NO')
                <button type="button" onclick="ir()" class="btn btn-default">
                    <i class="fa fa-arrow-left"></i> Regresar un nivel
                </button>
            @endif

            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalNuevaCarpeta">
                <i class="fa fa-folder"></i> Nueva carpeta
            </button>

            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalSubirArchivos">
                <i class="fa fa-upload"></i> Subir archivos
            </button>
        </div>
    </div>

    {!! Form::open(['route' => 'nube.list', 'method' => 'POST', 'id' => 'prev']) !!}
        <input type="hidden" name="prev" value="{{ $path }}" />
        @if($prev == 'NO')
            <input type="hidden" name="path" value="./nube/" />
        @else
            <input type="hidden" name="path" value="{{ $prev }}" />
        @endif
        <input type="hidden" name="id" value="{{ $id }}" />
    {!! Form::close() !!}

    <div class="row">
        <div class="col-sm-8 col-sm-offset-2">
            <div class="form-group nube-search">
                <input class="form-control" type="text" id="buscar" placeholder="Buscar en este directorio..." onkeyup="buscar()" />
            </div>
        </div>
    </div>

    <div class="row" id="txt">
        @if($files != null)
            @foreach($files as $f)
                <div class="col-xs-6 col-sm-4 col-md-2 nube-file" title="{{ $f['file'] }} ({{ $f['tamanio'] }})">
                    @if($f['type'] == 'FOLDER')
                        <i class="fa fa-{{ $f['icon'] }} nube-file-icon" style="color: {{ $f['color'] }}; cursor: pointer;" onclick="ingresar(this.id)" id="{{ $f['m'] }}" data-toggle="tooltip" data-placement="top" title="Ingresar"></i>
                    @else
                        <i class="fa fa-{{ $f['icon'] }} nube-file-icon" style="color: {{ $f['color'] }};"></i>
                    @endif

                    <p class="nube-file-name">{{ $f['file'] }} ({{ $f['tamanio'] }})</p>

                    @if($f['type'] != 'FOLDER')
                        <a class="nube-action" id="{{ url(str_replace('./', '', $f['path'])) }}" onclick="copiar(this.id)" data-toggle="tooltip" data-placement="top" title="Copiar enlace">
                            <i class="fa fa-clone"></i> Copiar enlace
                        </a>
                        <br>
                    @else
                        <form method="POST" action="{{ route('nube.list') }}" id="ingresar{{ $f['m'] }}">
                            <input type="hidden" name="prev" value="{{ $prev }}" />
                            <input type="hidden" name="path" value="{{ $f['path'] }}/" />
                            <input type="hidden" name="id" value="{{ $id }}" />
                            {{ csrf_field() }}
                        </form>
                    @endif

                    <form method="POST" action="{{ route('nube.delete') }}" id="borrar_{{ $f['m'] }}">
                        <input type="hidden" name="prev" value="{{ $prev }}" />
                        <input type="hidden" name="path" value="{{ $path }}" />
                        <input type="hidden" name="id" value="{{ $id }}" />
                        <input type="hidden" name="file_id" value="{{ $f['path'] }}" />
                        <input type="hidden" id="type" name="type" value="{{ $f['type'] }}" />
                        {{ csrf_field() }}
                    </form>

                    <a onclick="borrar(this.id)" id="{{ $f['m'] }}" class="nube-action text-danger" data-toggle="tooltip" data-placement="top" title="Eliminar">
                        <i class="fa fa-remove"></i> Eliminar
                    </a>
                </div>
            @endforeach
        @else
            <div class="col-sm-12">
                <h4 class="nube-empty">Directorio vacio.</h4>
            </div>
        @endif
    </div>

    <div class="modal fade" id="modalNuevaCarpeta" tabindex="-1" role="dialog" aria-labelledby="modalNuevaCarpetaLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="modalNuevaCarpetaLabel">Crear nueva carpeta</h4>
                </div>
                <div class="modal-body">
                    <form method="POST" action="{{ route('nube.nueva') }}" id="nueva">
                        <input type="hidden" name="prev" value="{{ $prev }}" />
                        <input type="hidden" name="path" value="{{ $path }}" />
                        <input type="hidden" name="id" value="{{ $id }}" />
                        <div class="form-group">
                            <label class="control-label">Nombre de la carpeta</label>
                            <span data-toggle="tooltip" title="Establece el nombre de la carpeta para tus archivos.">
                                <i class="fa fa-question-circle"></i>
                            </span>
                            <input type="text" id="name" name="name" class="form-control" required>
                        </div>
                        {{ csrf_field() }}
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="nueva()">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalSubirArchivos" tabindex="-1" role="dialog" aria-labelledby="modalSubirArchivosLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="modalSubirArchivosLabel">Subir archivos en esta carpeta</h4>
                </div>
                <div class="modal-body">
                    <form method="POST" action="{{ route('nube.upload') }}" id="upload" enctype="multipart/form-data">
                        <input type="hidden" name="prev" value="{{ $prev }}" />
                        <input type="hidden" name="path" value="{{ $path }}" />
                        <input type="hidden" name="id" value="{{ $id }}" />
                        <div class="form-group">
                            <label class="control-label">Archivos</label>
                            <span data-toggle="tooltip" title="Establece los archivos que deseas subir.">
                                <i class="fa fa-question-circle"></i>
                            </span>
                            {!! Form::file('archivo[]', ['class' => 'form-control has-feedback-left', 'required' => 'required', 'multiple' => 'multiple']) !!}
                        </div>
                        {{ csrf_field() }}
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="upload()">Guardar</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script type="text/javascript">
        $('[data-toggle="tooltip"]').tooltip({
            animated: 'fade',
            placement: 'right',
            html: true
        });

        var iconos = <?php echo json_encode($files); ?>;

        function buscar() {
            $("#txt").html("");
            var texto = $("#buscar").val().toLowerCase();
            var nuevoArray = [];

            if (iconos != null) {
                iconos.forEach(function(i) {
                    if (i.file.toLowerCase().indexOf(texto) != -1) {
                        nuevoArray.push(i);
                    }
                });
                arrayDraw(nuevoArray);
            } else {
                Swal.fire(
                    'Información',
                    'Directorio vacio',
                    'error'
                );
            }
        }

        function arrayDraw(array) {
            var html = "";

            array.forEach(function(i) {
                var publicPath = i.path.replace(/^\.\//, '');

                html = html + "<div class='col-xs-6 col-sm-4 col-md-2 nube-file' title='" + i.file + " (" + i.tamanio + ")'>";

                if (i.type == 'FOLDER') {
                    html = html + "<i class='fa fa-" + i.icon + " nube-file-icon' style='color: " + i.color + "; cursor: pointer;' onclick='ingresar(this.id)' id='" + i.m + "' data-toggle='tooltip' data-placement='top' title='Ingresar'></i>";
                } else {
                    html = html + "<i class='fa fa-" + i.icon + " nube-file-icon' style='color: " + i.color + ";'></i>";
                }

                html = html + "<p class='nube-file-name'>" + i.file + " (" + i.tamanio + ")</p>";

                if (i.type != 'FOLDER') {
                    html = html + "<a class='nube-action' id='<?php echo url(''); ?>/" + publicPath + "' onclick='copiar(this.id)' data-toggle='tooltip' data-placement='top' title='Copiar enlace'><i class='fa fa-clone'></i> Copiar enlace</a><br>";
                } else {
                    html = html + "<form method='POST' action='<?php echo route('nube.list'); ?>' id='ingresar" + i.m + "'>" +
                        "<input type='hidden' name='prev' value='<?php echo $prev; ?>' />" +
                        "<input type='hidden' name='path' value='" + i.path + "/' />" +
                        "<input type='hidden' name='id' value='<?php echo $id; ?>' />" +
                        "<?php echo $token; ?></form>";
                }

                html = html + "<form method='POST' action='<?php echo route('nube.delete'); ?>' id='borrar_" + i.m + "'>" +
                    "<input type='hidden' name='prev' value='<?php echo $prev; ?>' />" +
                    "<input type='hidden' name='path' value='<?php echo $path; ?>' />" +
                    "<input type='hidden' name='id' value='<?php echo $id; ?>' />" +
                    "<input type='hidden' name='file_id' value='" + i.path + "' />" +
                    "<input type='hidden' id='type' name='type' value='" + i.type + "' />" +
                    "<?php echo $token; ?></form>" +
                    "<a onclick='borrar(this.id)' id='" + i.m + "' class='nube-action text-danger' data-toggle='tooltip' data-placement='top' title='Eliminar'><i class='fa fa-remove'></i> Eliminar</a>" +
                    "</div>";
            });

            $("#txt").html(html);
            $('[data-toggle="tooltip"]').tooltip();
        }

        function copiar(text) {
            $("body").append("<input type='text' id='temp'>");
            $("#temp").val(text).select();
            document.execCommand("copy");
            $("#temp").remove();
            Swal.fire(
                'Información',
                'Ha copiado el enlace al portapapeles',
                'success'
            );
        }

        function ir() {
            $('#prev').submit();
        }

        function nueva() {
            var nombre = $("#name").val();
            if (nombre == "") {
                Swal.fire(
                    'Información',
                    'Debe indicar un nombre para la carpeta',
                    'error'
                );
                return;
            }
            $('#nueva').submit();
        }

        function ingresar(id) {
            $('#ingresar' + id).submit();
        }

        function upload() {
            $('#upload').submit();
        }

        function borrar(id) {
            var type = document.forms["borrar_" + id]['type'].value;
            var texto = type == 'FOLDER'
                ? 'Esta a punto de borrar un directorio y todo su contenido. Desea continuar?'
                : 'Esta a punto de borrar un archivo. Desea continuar?';

            Swal.fire({
                title: 'Confirmación',
                text: texto,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Si, borrar',
                cancelButtonText: 'No'
            }).then((result) => {
                if (result.value) {
                    $("#borrar_" + id).submit();
                }
            });
        }
    </script>
@endsection
