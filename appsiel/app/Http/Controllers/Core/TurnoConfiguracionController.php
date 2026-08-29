<?php

namespace App\Http\Controllers\Core;

use App\Core\Exceptions\TurnoIntegrityException;
use App\Core\Services\TurnoConfigurationService;
use App\Core\TurnoConfiguracion;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TurnoConfiguracionController extends Controller
{
    protected $service;

    public function __construct(TurnoConfigurationService $service)
    {
        $this->middleware('auth');
        $this->service = $service;
    }

    public function store(Request $request)
    {
        $this->authorizeManagement();
        $data = $this->configurationData($request);

        try {
            $result = $this->service->configure($data);
        } catch (TurnoIntegrityException $exception) {
            $this->logFailure('store', $data, $exception);
            return redirect()->back()->withInput()->with('mensaje_error', $exception->getMessage());
        }

        $this->logChange('created_or_updated', $result['configuration'], $result['warnings']);
        return $this->redirectToRecord($request, $result['configuration'])
            ->with('flash_message', $this->successMessage('Configuración de turnos guardada.', $result['warnings']));
    }

    public function update(Request $request, $id)
    {
        $this->authorizeManagement();
        $configuration = $this->findForCurrentCompany($id);
        if (is_null($configuration)) {
            return redirect()->back()->with('mensaje_error', 'La configuración de turnos no existe o pertenece a otra empresa.');
        }

        $data = array(
            'core_empresa_id' => (int)$configuration->core_empresa_id,
            'modulo' => $configuration->modulo,
            'contexto_tipo' => $configuration->contexto_tipo,
            'contexto_id' => (int)$configuration->contexto_id,
        );
        $data['modo'] = $request->input('modo');
        $data['creado_por'] = $configuration->creado_por;
        $data['modificado_por'] = Auth::user()->email;

        try {
            $result = $this->service->configure($data);
        } catch (TurnoIntegrityException $exception) {
            $this->logFailure('update', $data, $exception);
            return redirect()->back()->withInput()->with('mensaje_error', $exception->getMessage());
        }

        $this->logChange('updated', $result['configuration'], $result['warnings']);
        return $this->redirectToRecord($request, $result['configuration'])
            ->with('flash_message', $this->successMessage('Configuración de turnos modificada.', $result['warnings']));
    }

    public function destroy(Request $request, $id)
    {
        $this->authorizeManagement();
        $configuration = $this->findForCurrentCompany($id);
        if (is_null($configuration)) {
            return redirect()->back()->with('mensaje_error', 'La configuración de turnos no existe o pertenece a otra empresa.');
        }

        try {
            $this->service->remove($configuration, Auth::id());
        } catch (TurnoIntegrityException $exception) {
            return redirect()->back()->with('mensaje_error', $exception->getMessage());
        }

        return redirect('web?id=' . (int)$request->input('id') . '&id_modelo=' . (int)$request->input('id_modelo'))
            ->with('flash_message', 'Configuración eliminada. El alcance vuelve a su configuración heredada o a TRADICIONAL.');
    }

    protected function configurationData(Request $request)
    {
        return array(
            'core_empresa_id' => (int)Auth::user()->empresa_id,
            'modulo' => trim((string)$request->input('modulo')),
            'contexto_tipo' => trim((string)$request->input('contexto_tipo')),
            'contexto_id' => (int)$request->input('contexto_id'),
            'modo' => trim((string)$request->input('modo')),
            'creado_por' => Auth::user()->email,
            'modificado_por' => Auth::user()->email,
        );
    }

    protected function findForCurrentCompany($id)
    {
        return TurnoConfiguracion::where('id', (int)$id)
            ->where('core_empresa_id', (int)Auth::user()->empresa_id)->first();
    }

    protected function authorizeManagement()
    {
        if (!Auth::user()->can('turnos.configuraciones.gestionar')) {
            abort(403, 'No tiene permiso para administrar la configuración de turnos.');
        }
    }

    protected function redirectToRecord(Request $request, TurnoConfiguracion $configuration)
    {
        return redirect('web/' . $configuration->id . '?id=' . (int)$request->input('url_id')
            . '&id_modelo=' . (int)$request->input('url_id_modelo'));
    }

    protected function successMessage($message, array $warnings)
    {
        if (empty($warnings)) {
            return $message;
        }
        return $message . '<br><strong>Advertencias:</strong> ' . e(implode(' ', $warnings));
    }

    protected function logChange($action, TurnoConfiguracion $configuration, array $warnings)
    {
        Log::info('turnos.configuration_' . $action, array(
            'empresa_id' => (int)$configuration->core_empresa_id,
            'modulo' => $configuration->modulo,
            'contexto_tipo' => $configuration->contexto_tipo,
            'contexto_id' => (int)$configuration->contexto_id,
            'modo' => $configuration->modo,
            'usuario_id' => (int)Auth::id(),
            'warnings' => $warnings,
        ));
    }

    protected function logFailure($action, array $data, TurnoIntegrityException $exception)
    {
        Log::warning('turnos.configuration_' . $action . '_failed', array(
            'empresa_id' => isset($data['core_empresa_id']) ? (int)$data['core_empresa_id'] : null,
            'modulo' => isset($data['modulo']) ? $data['modulo'] : null,
            'contexto_tipo' => isset($data['contexto_tipo']) ? $data['contexto_tipo'] : null,
            'contexto_id' => isset($data['contexto_id']) ? (int)$data['contexto_id'] : null,
            'modo' => isset($data['modo']) ? $data['modo'] : null,
            'usuario_id' => Auth::check() ? (int)Auth::id() : null,
            'error' => $exception->getMessage(),
        ));
    }
}
