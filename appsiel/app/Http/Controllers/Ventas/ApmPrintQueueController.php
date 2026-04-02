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
                'can_retire' => $this->userCanRetireQueue()
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
            $prepared = $this->service->prepareReprint($jobId);

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

    protected function userCanRetireQueue()
    {
        $user = Auth::user();

        return !is_null($user) && $user->can(self::RETIRE_PERMISSION);
    }
}
