<br/><br/>
<div class="row">
    <div class="col-md-8 col-md-offset-2" style="vertical-align: center; border: 1px solid gray;">
        <h3>Responder</h3>
        <hr>
        <form action="{{ route('messages.update', $thread->id) }}" method="post">
            {{ method_field('put') }}
            {{ csrf_field() }}
                
            <!-- Message Form Input -->
            <div class="form-group">
                <textarea name="message" class="form-control" required="required">{{ old('message') }}</textarea>
            </div>

            <!-- Submit Form Input -->
            <div class="form-group">
                <button type="submit" class="btn btn-primary form-control">Enviar</button>
            </div>
        </form>
    </div>
</div>