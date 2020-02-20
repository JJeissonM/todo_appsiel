<?php

namespace App\Http\Controllers\Matriculas;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Matriculas\Estudiante;
use \Excel;
use Validator;
use JavaScript;
use App\Http\Requests\ExcelRequest;

class ExcelEstudianteController extends Controller
{
    protected $request;
    protected $estudiante;
    protected $data = [];
    protected $i = 1;
    protected $errors;
    protected $input;
    protected $rules;

    public function __construct(Request $request, Estudiante $estudiante)
    {
        $this->request = $request;
        $this->estudiante = $estudiante;
        $this->errors = [];
        $this->data = [];
        $this->rules = [
            'nombres'       => 'required|string|max:100',
            'apellido1'       => 'required|string|max:100',
            'apellido2'       => 'required|string|max:100',
            'doc_identidad'       => 'required|string|max:100',
            'genero'       => 'required|string|max:10',
            'direccion1'       => 'required|string|max:100',
            'barrio'       => 'required|string|max:100',
            'telefono1'       => 'required|string|max:100',
            'fecha_nacimiento'       => 'required|date',
            'ciudad_nacimiento'       => 'required|string|max:100',
            'mama'       => 'required|string|max:100',
            'ocupacion_mama'       => 'required|string|max:100',
            'telefono_mama'       => 'required|string|max:100',
            'email_mama'       => 'required|string|max:100',
            'papa'       => 'required|string|max:100',
            'ocupacion_papa'       => 'required|string|max:100',
            'telefono_papa'       => 'required|string|max:100',
            'email_papa'       => 'required|string|max:100',
        ];

    }

    public function index()
    {
        //
    }

    public function importFile(Request $request, Estudiante $estudiante)
    {
        $this->processData($request);

        return view('matriculas.estudiantes.importar_excel.import', [ 'data' => $this->data, 'errors' => $this->errors, 'input' => $this->input]);
    }

    /**
     * Validate cell against the rules.
     *
     * @param array $data
     * @param array $rules
     *
     * @return array
     */
    protected function validateCell(array $data, array $rules)
    {
        // Perform Validation
        $validator = \Validator::make($data, $rules);

        if ($validator->fails()) {
            $errorMessages = $validator->errors()->messages();

            // crete error message by using key and value
            foreach ($errorMessages as $key => $value) {
                $errorMessages = $value[0];
            }

            return $errorMessages;
        }

        return [];
    }

    public function store(Request $request)
    {
        dd('Cambiar función. Pendiente por crear terceros.');

        $colegio = Colegio::where('empresa_id',Auth::user()->empresa_id)->get();
        $colegio = $colegio[0];

        $this->validateData($request);

        if (!empty($this->errors)) {
            return view('matriculas.estudiantes.importar_excel.import', [
                'data' => $this->data,
                'errors' => $this->errors,
                'input' => $this->input
            ]);
        }

        foreach ($this->data as $data) {

            $data = array_except($data, ['id']);

            $estudiante = new Estudiante;
            $estudiante->id_colegio = $colegio->id;//$data['id_colegio'];
            $estudiante->nombres = $data['nombres'];
            $estudiante->apellido1 = $data['apellido1'];
            $estudiante->apellido2 = $data['apellido2'];
            $estudiante->tipo_doc_id = 11;//$data['tipo_doc_id'];
            $estudiante->doc_identidad = $data['doc_identidad'];
            $estudiante->genero = $data['genero'];
            $estudiante->direccion1 = $data['direccion1'];
            $estudiante->barrio = $data['barrio'];
            $estudiante->telefono1 = $data['telefono1'];
            $estudiante->fecha_nacimiento = $data['fecha_nacimiento'];
            $estudiante->ciudad_nacimiento = $data['ciudad_nacimiento'];
            $estudiante->mama = $data['mama'];
            $estudiante->ocupacion_mama = $data['ocupacion_mama'];
            $estudiante->telefono_mama = $data['telefono_mama'];
            $estudiante->email_mama = $data['email_mama'];
            $estudiante->papa = $data['papa'];
            $estudiante->ocupacion_papa = $data['ocupacion_papa'];
            $estudiante->telefono_papa = $data['telefono_papa'];
            $estudiante->email_papa = $data['email_papa'];
            $estudiante->save();
        }

        return redirect('/matriculas/estudiantes/importar_excel')->with('info', 'Datos Guardados');
    }

    protected function processData($request)
    {
        Excel::selectSheetsByIndex(0)->load($request->excel, function($reader) {
            
            //$reader->formatDates(true, 'd-m-Y');

            $excel = $reader->get();

            $this->errors = [];
            $this->rowNumber = 0;

            $excel->each(function($row) {

                $this->data[$this->rowNumber] = [
                    'nombres'       => $row->nombres,
                    'apellido1'       => $row->apellido1,
                    'apellido2'       => $row->apellido2,
                    'doc_identidad'       => $row->doc_identidad,
                    'genero'       => $row->genero,
                    'direccion1'       => $row->direccion1,
                    'barrio'       => $row->barrio,
                    'telefono1'       => $row->telefono1,
                    'fecha_nacimiento'       => $row->fecha_nacimiento,
                    'ciudad_nacimiento'       => $row->ciudad_nacimiento,
                    'mama'       => $row->mama,
                    'ocupacion_mama'       => $row->ocupacion_mama,
                    'telefono_mama'       => $row->telefono_mama,
                    'email_mama'       => $row->email_mama,
                    'papa'       => $row->papa,
                    'ocupacion_papa'       => $row->ocupacion_papa,
                    'telefono_papa'       => $row->telefono_papa,
                    'email_papa'       => $row->email_papa,
                ];

                foreach ($this->data[$this->rowNumber] as $key => $value) {

                    $error = $this->validateCell([$key => $value], [$key => $this->rules[$key]]);

                    if (!empty($error)) {
                        $this->errors[$this->rowNumber][$key] = $error;
                    }
                    
                }

                $this->data[$this->rowNumber]['id'] = $this->rowNumber;

                $this->rowNumber++;
            });
        });
    }

    protected function validateData($request)
    {
        $data = $request->except('_token');

        $this->errors = [];
        $this->rowNumber = 0;

        foreach ($data as $dataKey => $value) {

            $i = 0;

            foreach ($value as $item) {

                $error = $this->validateCell([$dataKey => $item], [$dataKey => $this->rules[$dataKey]]);

                if (!empty($error)) {
                    $this->errors[$i][$dataKey] = $error;
                }

                $this->data[$i]['id'] = $i;

                $this->data[$i][$dataKey] = $item;

                $i++;
            }
        }
    }
}