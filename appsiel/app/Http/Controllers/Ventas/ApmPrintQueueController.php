<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use App\Ventas\Services\ApmPrintQueueService;
use Illuminate\Http\Request;

class ApmPrintQueueController extends Controller
{
    protected $service;

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

        return response()->json(['data' => $jobs->values()]);
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
}