<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use App\Ventas\Services\ApmPrintQueueService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ApmPrintQueueController extends Controller
{
    protected $service;
    const RETIRE_PERMISSION = 'vtas_apm_retirar_cola_impresion';
    const MANAGE_PERMISSION = 'vtas_apm_print_jobs';

    public function __construct(ApmPrintQueueService $service)
    {
        $this->middleware('auth');
        $this->service = $service;
    }

    public function index()
    {
        $jobs = $this->service->getPendingJobs()->map(function ($job) {
            return $this->service->serializeJob($job);
        });

        return response()->json([
            'data' => $jobs->values(),
            'permissions' => [
                'can_retire' => $this->userCanRetireQueue(),
                'can_manage' => $this->userCanManageQueue()
            ]
        ]);
    }

    public function prepare(Request $request)
    {
        try {
            $prepared = $this->service->prepareJob(
                (array) $request->input('payload', []),
                (array) $request->input('document_meta', [])
            );

            return response()->json([
                'job' => $this->service->serializeJob($prepared['job']),
                'payload' => $prepared['payload']
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function prepareReprint($jobId)
    {
        try {
            $prepared = $this->service->prepareReprint(
                $jobId,
                request()->input('force_copy') ? true : false,
                request()->input('retry_only') ? true : false
            );

            return response()->json([
                'job' => $this->service->serializeJob($prepared['job']),
                'payload' => $prepared['payload']
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function markPrinted($jobId)
    {
        try {
            $job = $this->service->markPrinted($jobId);
            return response()->json(['job' => $this->service->serializeJob($job)]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function markAttempted($jobId, Request $request)
    {
        try {
            $job = $this->service->markAttempted($jobId, $request->input('message', 'Trabajo enviado a APM. Pendiente confirmar impresion fisica.'));
            return response()->json(['job' => $this->service->serializeJob($job)]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function markFailed($jobId, Request $request)
    {
        try {
            $job = $this->service->markFailed($jobId, $request->input('error_message', 'Error desconocido al imprimir con APM.'));
            return response()->json(['job' => $this->service->serializeJob($job)]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function markRetired($jobId)
    {
        if (!$this->userCanRetireQueue()) {
            return response()->json(['message' => 'No tiene permiso para retirar documentos de la cola APM.'], 403);
        }

        try {
            $job = $this->service->markRetired($jobId);
            return response()->json(['job' => $this->service->serializeJob($job)]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function catalogMarkPending($jobId)
    {
        if (!$this->userCanManageQueue()) {
            return $this->redirectForbidden();
        }

        try {
            $this->service->markPending($jobId, true);
            return $this->redirectBackWithMessage('Trabajo reenviado a la cola APM.');
        } catch (\Exception $e) {
            return $this->redirectBackWithError($e->getMessage());
        }
    }

    public function catalogMarkPrinted($jobId)
    {
        if (!$this->userCanManageQueue()) {
            return $this->redirectForbidden();
        }

        try {
            $this->service->markPrinted($jobId);
            return $this->redirectBackWithMessage('Trabajo marcado como impreso.');
        } catch (\Exception $e) {
            return $this->redirectBackWithError($e->getMessage());
        }
    }

    public function catalogMarkRetired($jobId)
    {
        if (!$this->userCanRetireQueue()) {
            return $this->redirectForbidden();
        }

        try {
            $this->service->markRetired($jobId);
            return $this->redirectBackWithMessage('Trabajo retirado de la cola APM.');
        } catch (\Exception $e) {
            return $this->redirectBackWithError($e->getMessage());
        }
    }

    public function catalogMarkCancelled($jobId)
    {
        if (!$this->userCanManageQueue()) {
            return $this->redirectForbidden();
        }

        try {
            $this->service->markCancelled($jobId);
            return $this->redirectBackWithMessage('Trabajo cancelado.');
        } catch (\Exception $e) {
            return $this->redirectBackWithError($e->getMessage());
        }
    }

    public function catalogDelete($jobId)
    {
        if (!$this->userCanManageQueue()) {
            return $this->redirectForbidden();
        }

        try {
            $this->service->deleteJob($jobId);
            return $this->redirectBackWithMessage('Trabajo eliminado.');
        } catch (\Exception $e) {
            return $this->redirectBackWithError($e->getMessage());
        }
    }

    protected function userCanRetireQueue()
    {
        $user = Auth::user();

        return !is_null($user) && ($this->userCanManageQueue() || $user->can(self::RETIRE_PERMISSION));
    }

    protected function userCanManageQueue()
    {
        $user = Auth::user();

        return !is_null($user) && (
            $user->hasRole('SuperAdmin') ||
            $user->hasRole('Administrador') ||
            $user->can(self::RETIRE_PERMISSION) ||
            $user->can(self::MANAGE_PERMISSION)
        );
    }

    protected function redirectBackWithMessage($message)
    {
        return redirect()->back()->with('flash_message', $message);
    }

    protected function redirectBackWithError($message)
    {
        return redirect()->back()->with('mensaje_error', $message);
    }

    protected function redirectForbidden()
    {
        return $this->redirectBackWithError('No tiene permiso para gestionar trabajos de impresion APM.');
    }
}
