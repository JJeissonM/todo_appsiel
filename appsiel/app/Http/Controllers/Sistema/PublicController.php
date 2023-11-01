<?php

namespace App\Http\Controllers\Sistema;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

use Input;

use App\Sistema\Modelo;

class PublicController extends Controller
{
    public function get_example()
    {
        $data = json_decode('{
            "app_label": "Ventas",
            "model_label": "Clases de vendedores",
            "model_name": "sales.seller.class",
            "model_table_headers": [
              {
                "Header": "Descripción",
                "accessor": "label",
                "type": "text"
              },
              {
                "Header": "Estado",
                "accessor": "state",
                "type": "text"
              }
            ],
            "model_table_rows": {
              "current_page": 1,
              "last_page": 1,
              "total_records": 2,
              "records": [
                {
                  "id": 1,
                  "label": "Autoventa",
                  "state": {
                    "id": "Activo",
                    "label": "Activo"
                  },
                  "created_at": "2021-11-26T23:16:28.000000Z",
                  "updated_at": null
                },
                {
                  "id": 2,
                  "label": "Preventa",
                  "state": {
                    "id": "Activo",
                    "label": "Activo"
                  },
                  "created_at": "2021-11-26T23:16:28.000000Z",
                  "updated_at": null
                }
              ],
              "first_item": 1,
              "last_item": 2,
              "previous_page_url": null,
              "next_page_url": null
            },
            "model_fields": [
              {
                "id": 29,
                "label": "Descripción/Nombre",
                "type": "text",
                "name": "label",
                "options": null,
                "value": null,
                "attributes": null,
                "definition": null,
                "created_at": "2022-01-14T21:19:57.000000Z",
                "updated_at": "2022-03-26T13:30:47.000000Z",
                "pivot": {
                  "model_id": "97",
                  "field_id": "29",
                  "position": "1",
                  "required": "1",
                  "editable": "1",
                  "unique": "1"
                }
              },
              {
                "id": 7,
                "label": "Estado",
                "type": "select",
                "name": "state",
                "options": "[[\"Activo\",\"Activo\"],[\"Inactivo\",\"Inactivo\"]]",
                "value": "Activo",
                "attributes": "",
                "definition": "",
                "created_at": "2021-12-02T02:44:14.000000Z",
                "updated_at": "2021-12-02T02:44:14.000000Z",
                "pivot": {
                  "model_id": "97",
                  "field_id": "7",
                  "position": "2",
                  "required": "1",
                  "editable": "1",
                  "unique": "1"
                }
              }
            ],
            "model_actions": [
              {
                "id": 1,
                "label": "Crear",
                "name": "show.create.form",
                "type": "create",
                "method": "POST",
                "prefix": "",
                "icon": "fas fa-plus",
                "data_name_destiny": null,
                "form_action": null,
                "message": null,
                "required": null,
                "created_at": "2021-12-02T03:46:30.000000Z",
                "updated_at": "2023-05-29T23:36:10.000000Z",
                "pivot": {
                  "model_id": "97",
                  "action_id": "1"
                }
              },
              {
                "id": 2,
                "label": "Modificar",
                "name": "show.edit.form",
                "type": "edit",
                "method": "PUT",
                "prefix": "",
                "icon": "fas fa-pen",
                "data_name_destiny": null,
                "form_action": null,
                "message": null,
                "required": null,
                "created_at": "2021-12-02T03:46:30.000000Z",
                "updated_at": "2021-12-02T03:46:30.000000Z",
                "pivot": {
                  "model_id": "97",
                  "action_id": "2"
                }
              },
              {
                "id": 3,
                "label": "Eliminar",
                "name": "delete.record",
                "type": "delete",
                "method": "DELETE",
                "prefix": "",
                "icon": "fas fa-trash-alt",
                "data_name_destiny": null,
                "form_action": null,
                "message": null,
                "required": null,
                "created_at": "2021-12-02T03:46:30.000000Z",
                "updated_at": "2021-12-02T03:46:30.000000Z",
                "pivot": {
                  "model_id": "97",
                  "action_id": "3"
                }
              }
            ],
            "sections": [],
            "data_name": "",
            "view": "server_side",
            "demo_company_id": 2
          }');

        return response()->json($data, 200);
    }

    public function post_example(Request $request)
    {
        $data = [
            'status' => 'success',
            'message' => 'Datos recibidos correctamente.',
            'data' => $request->all()
        ];

        return response()->json($data, 200);
    }

}