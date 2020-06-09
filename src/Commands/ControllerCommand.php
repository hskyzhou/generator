<?php 

namespace HskyZhou\Generator;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Traitor\Traitor;
use Artisan;
use Illuminate\Support\Str;

class ControllerCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hsky:controller 
                                {name : 不需要带Controller}
                                {--skip-request : 不生成request}
                                {--skip-service : 不生成service}
                                {--sn|serviceName : 自定义service的名称,不需要带Service}
                                {--rn|requestName : 自定义request的名称,不需要带Request}
                            ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '自动生成controller文件';

    protected $type = 'controller';

    protected function getStub()
    {
        return __DIR__ . '/stubs/controller.stub';
    }

    protected function buildClass($name) {
        $replace = [];

        if ($this->option('skip-request')) {
            $replace = array_merge($replace, [
                'DummyFullRequestClass' => '',
                'DummyRequestClass' => '',
                'DummyRequestVariable' => '',
                'DummyDelimiter' => '',
            ]);
        } else {
            $replace = $this->buildRequestReplacements($replace);
        }

        if ($this->option('skip-service')) {
            $replace = array_merge($replace, [
                'DummyFullServiceClass' => '',
                'DummyServiceClass' => '',
                'DummyServiceVariable' => '',
                'DummyDelimiter' => '',
            ]);
        } else {
            $replace = $this->buildServiceReplacements($replace);
        }

        return str_replace(
            array_keys($replace), array_values($replace), parent::buildClass($name)
        );
    }

    protected function buildRequestReplacements($replace) {
        $request = $this->option('requestName') ? $this->option('requestName') : $this->argument('name');

        $request = $request . 'Request';

        $requestClass = $this->parseRequest($request);

        if (!class_exists($requestClass)) {
            // if ($this->confirm("A {$requestClass} request does not exist. Do you want to generate it?", true)) {
                $this->call("make:request", ['name' => $requestClass]);
            // }
        }

        return array_merge($replace, [
            'DummyFullRequestClass' => 'use ' . $requestClass . ';',
            'DummyRequestClass' => class_basename($requestClass),
            'DummyRequestVariable' => '$' . lcfirst(class_basename($requestClass)),
            'DummyDelimiter' => ',',
        ]);
    }

    /**
     * 解析service
     * @param  [type] $service [description]
     * @return [type]          [description]
     */
    protected function parseRequest($request) {
        $request = trim(str_replace('/', '\\', $request), '\\');

        if (! Str::startsWith($request, $rootNamespace = $this->getRequestNamespace())) {
            $request = $rootNamespace.$request;
        }

        return $request;
    }

    protected function getRequestNamespace()
    {
        return 'App\\Http\\Requests\\';
    }

    protected function buildServiceReplacements($replace) {

        $service = $this->option('serviceName') ? $this->option('serviceName') : basename(dirname($this->argument('name')));
        $service = $service;

        $serviceClass = $this->parseService($service);

        // dd($serviceClass);
        if (!class_exists($serviceClass)) {
            // if ($this->confirm("A {$serviceClass} service does not exist. Do you want to generate it?", true)) {
                $this->call("hsky:service", ['name' => $serviceClass]);
            // }
        }

        $serviceClass = $serviceClass . 'Service';

        return array_merge($replace, [
            'DummyFullServiceClass' => 'use ' . $serviceClass . ';',
            'DummyServiceClass' => class_basename($serviceClass),
            'DummyServiceVariable' => '$' . lcfirst(class_basename($serviceClass)),
            'DummyDelimiter' => ',',
        ]);
    }

    /**
     * 解析service
     * @param  [type] $service [description]
     * @return [type]          [description]
     */
    protected function parseService($service) {
        $service = trim(str_replace('/', '\\', $service), '\\');

        if (! Str::startsWith($service, $rootNamespace = $this->getServiceNamespace())) {
            $service = $rootNamespace.$service;
        }

        return $service;
    }

    protected function getServiceNamespace()
    {
        return 'App\\Services\\';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Http\Controllers';
    }

    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getNameInput()
    {
        return trim($this->argument('name') . 'Controller');
    }
}
