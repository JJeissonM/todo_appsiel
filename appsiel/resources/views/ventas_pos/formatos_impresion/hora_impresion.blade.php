<div style="width: 100%; text-align: center; font-size: 10px; margin-top: 8px;">
    <b>Hora impresión:</b> {{ \Carbon\Carbon::now(config('app.timezone'))->format('d/m/Y h:i:s A') }}
</div>
