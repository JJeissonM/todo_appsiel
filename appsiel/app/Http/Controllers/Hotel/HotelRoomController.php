<?php

namespace App\Http\Controllers\Hotel;

use App\Hotel\HotelRoom;
use App\Http\Controllers\Controller;
use App\Inventarios\InvProducto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HotelRoomController extends Controller
{
    public function index()
    {
        $empresaId = Auth::user()->empresa_id;
        $rooms = HotelRoom::where('empresa_id', $empresaId)->with('product')->orderBy('room_number')->paginate(20);
        $miga_pan = $this->breadcrumb('Habitaciones');

        return view('hotel.rooms.index', compact('rooms', 'miga_pan'));
    }

    public function create()
    {
        $room = new HotelRoom();
        $types = HotelRoom::options(HotelRoom::roomTypes());
        $statuses = HotelRoom::options(HotelRoom::statuses());
        $products = $this->productsList();
        $miga_pan = $this->breadcrumb('Crear habitacion');

        return view('hotel.rooms.create', compact('room', 'types', 'statuses', 'products', 'miga_pan'));
    }

    public function store(Request $request)
    {
        $empresaId = Auth::user()->empresa_id;
        $this->validate($request, array(
            'room_number' => 'required|max:20|unique:hotel_rooms,room_number,NULL,id,empresa_id,' . $empresaId,
            'room_type' => 'required|in:' . implode(',', HotelRoom::roomTypes()),
            'inv_producto_id' => 'required|exists:inv_productos,id',
            'capacity' => 'required|integer|min:1',
        ));

        $data = $request->all();
        $data['empresa_id'] = $empresaId;
        $data['status'] = isset($data['status']) && $data['status'] != '' ? $data['status'] : HotelRoom::STATUS_DISPONIBLE;
        $data['is_active'] = $request->has('is_active') ? 1 : 0;

        $room = HotelRoom::create($data);

        return redirect('hotel/rooms/' . $room->id)->with('flash_message', 'Habitacion creada correctamente.');
    }

    public function show($id)
    {
        $room = $this->findRoom($id);
        $miga_pan = $this->breadcrumb('Habitacion ' . $room->room_number);

        return view('hotel.rooms.show', compact('room', 'miga_pan'));
    }

    public function edit($id)
    {
        $room = $this->findRoom($id);
        $types = HotelRoom::options(HotelRoom::roomTypes());
        $statuses = HotelRoom::options(HotelRoom::statuses());
        $products = $this->productsList();
        $miga_pan = $this->breadcrumb('Editar habitacion');

        return view('hotel.rooms.edit', compact('room', 'types', 'statuses', 'products', 'miga_pan'));
    }

    public function update(Request $request, $id)
    {
        $room = $this->findRoom($id);
        $empresaId = Auth::user()->empresa_id;

        $this->validate($request, array(
            'room_number' => 'required|max:20|unique:hotel_rooms,room_number,' . $room->id . ',id,empresa_id,' . $empresaId,
            'room_type' => 'required|in:' . implode(',', HotelRoom::roomTypes()),
            'inv_producto_id' => 'required|exists:inv_productos,id',
            'capacity' => 'required|integer|min:1',
        ));

        $data = $request->all();
        $data['is_active'] = $request->has('is_active') ? 1 : 0;
        $room->fill($data);
        $room->save();

        return redirect('hotel/rooms/' . $room->id)->with('flash_message', 'Habitacion actualizada correctamente.');
    }

    public function changeStatus(Request $request, $id)
    {
        $room = $this->findRoom($id);
        $this->validate($request, array('status' => 'required|in:' . implode(',', HotelRoom::statuses())));

        DB::transaction(function () use ($room, $request) {
            $room->status = $request->status;
            $room->save();
        });

        if ($request->return_to != '') {
            return redirect($request->return_to)->with('flash_message', 'Estado actualizado correctamente.');
        }

        return redirect('hotel/rooms/' . $room->id)->with('flash_message', 'Estado actualizado correctamente.');
    }

    public function deactivate($id)
    {
        $room = $this->findRoom($id);
        $room->is_active = 0;
        $room->save();

        return redirect('hotel/rooms')->with('flash_message', 'Habitacion desactivada correctamente.');
    }

    private function findRoom($id)
    {
        return HotelRoom::where('empresa_id', Auth::user()->empresa_id)->where('id', $id)->firstOrFail();
    }

    private function productsList()
    {
        $rows = InvProducto::where('core_empresa_id', Auth::user()->empresa_id)->where('estado', 'Activo')->orderBy('descripcion')->get();
        $options = array('' => '');
        foreach ($rows as $row) {
            $options[$row->id] = $row->id . ' - ' . $row->descripcion;
        }
        return $options;
    }

    private function breadcrumb($label)
    {
        return array(
            array('url' => 'hotel/rooms', 'etiqueta' => 'Hotel'),
            array('url' => 'NO', 'etiqueta' => $label),
        );
    }
}
