<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label>Numero</label>
            <input type="text" name="room_number" class="form-control" value="{{ old('room_number', $room->room_number) }}" required>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Tipo</label>
            <select name="room_type" class="form-control" required>
                @foreach($types as $key => $label)
                    <option value="{{ $key }}" {{ old('room_type', $room->room_type) == $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Producto/servicio asociado</label>
            <select name="inv_producto_id" class="form-control" required>
                @foreach($products as $key => $label)
                    <option value="{{ $key }}" {{ old('inv_producto_id', $room->inv_producto_id) == $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label>Piso</label>
            <input type="text" name="floor" class="form-control" value="{{ old('floor', $room->floor) }}">
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Capacidad</label>
            <input type="number" name="capacity" min="1" class="form-control" value="{{ old('capacity', $room->capacity ? $room->capacity : 1) }}">
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Estado</label>
            <select name="status" class="form-control">
                @foreach($statuses as $key => $label)
                    <option value="{{ $key }}" {{ old('status', $room->status ? $room->status : 'DISPONIBLE') == $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-3">
        <div class="checkbox" style="margin-top: 28px;">
            <label><input type="checkbox" name="is_active" value="1" {{ old('is_active', is_null($room->is_active) ? 1 : $room->is_active) ? 'checked' : '' }}> Activa</label>
        </div>
    </div>
</div>
<div class="form-group">
    <label>Descripcion</label>
    <textarea name="description" class="form-control" rows="3">{{ old('description', $room->description) }}</textarea>
</div>
