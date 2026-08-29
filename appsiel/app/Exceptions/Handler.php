<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use App\Core\Exceptions\TurnoIntegrityException;
use App\Core\Exceptions\TurnoRequiredException;
use App\Core\Exceptions\TurnoStateException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
        TurnoIntegrityException::class,
        TurnoRequiredException::class,
        TurnoStateException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        if ($e instanceof TurnoIntegrityException || $e instanceof TurnoRequiredException || $e instanceof TurnoStateException) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(array(
                    'error' => 'turno_operativo_invalido',
                    'message' => $e->getMessage(),
                ), 422);
            }
            return redirect()->back()->withInput()->with('mensaje_error', $e->getMessage());
        }

        if($this->isHttpException($e))
        {
            switch ($e->getStatusCode()) 
                {
                // not found
                case 404:
                return redirect()->guest( 'pagina_no_encontrada'.$request->getpathInfo() );
                break;

                // internal error
                case '500':
                return redirect()->guest('/');
                break;

                default:
                    return $this->renderHttpException($e);
                break;
            }
        }
        else
        {
                return parent::render($request, $e);
        }
    }
}
