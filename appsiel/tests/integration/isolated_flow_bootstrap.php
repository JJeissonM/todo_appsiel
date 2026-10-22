<?php

// Explicit isolated bootstrap: no .env, application bootstrap, or external DB.
$flowRoot = dirname(dirname(__DIR__));
require $flowRoot.'/vendor/autoload.php';
spl_autoload_register(function ($class) use ($flowRoot) {
    if (strpos($class, 'App\\') === 0) {
        $file = $flowRoot.'/app/'.str_replace('\\', '/', substr($class, 4)).'.php';
        if (is_file($file)) require $file;
    }
});
require_once $flowRoot.'/app/helpers.php';

class IsolatedFlowKernel extends Illuminate\Foundation\Http\Kernel
{
    protected $bootstrappers = [];
    protected function reportException($e) {}
    protected function renderException($request, $e) { throw $e; }
}

class IsolatedFlowExceptionHandler implements Illuminate\Contracts\Debug\ExceptionHandler
{
    public function report(Exception $e) { throw $e; }
    public function render($request, Exception $e) { throw $e; }
    public function renderForConsole($output, Exception $e) { throw $e; }
}

// Existing tests use TestCase; this alternative deliberately avoids bootstrap/app.php.
class TestCase extends Illuminate\Foundation\Testing\TestCase
{
    public function createApplication()
    {
        $root = dirname(dirname(__DIR__));
        $app = new Illuminate\Foundation\Application($root);
        $app->instance('env', 'testing');
        $config = [];
        foreach (glob($root.'/config/*.php') as $file) {
            // Excel is outside this suite and references its optional package at load time.
            if (basename($file) !== 'excel.php') $config[basename($file, '.php')] = require $file;
        }
        $config['database']['default'] = 'isolated';
        $config['database']['connections'] = [
            'isolated' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''],
        ];
        $config['session']['driver'] = 'array';
        $config['cache']['default'] = 'array';
        $config['app']['url'] = 'http://localhost';
        $app->instance('config', new Illuminate\Config\Repository($config));
        $app->instance('request', Illuminate\Http\Request::create('http://localhost'));
        Illuminate\Support\Facades\Facade::clearResolvedInstances();
        Illuminate\Support\Facades\Facade::setFacadeApplication($app);
        foreach (['Database', 'Auth', 'Cookie', 'Encryption', 'Session', 'Cache',
            'Filesystem', 'View', 'Translation', 'Validation'] as $provider) {
            $app->register('Illuminate\\'.$provider.'\\'.$provider.'ServiceProvider');
        }
        $app->singleton('Illuminate\Contracts\Http\Kernel', IsolatedFlowKernel::class);
        $app->singleton('Illuminate\Contracts\Debug\ExceptionHandler', IsolatedFlowExceptionHandler::class);
        // Only the HTTP route exercised by the accounting regression fixture.
        $app['router']->post('/contab_crear_nota_cierre_ejercicio', [
            'uses' => 'App\Http\Controllers\Contabilidad\ProcesosController@crear_nota_cierre_ejercicio',
        ]);
        $app->boot();
        return $app;
    }
}
