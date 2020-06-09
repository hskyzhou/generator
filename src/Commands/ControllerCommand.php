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
                                {name} 
                                {--r|request= : 自定义request}
                                {--s|service= : 自定义service}';

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

        if ($this->option('request')) {
            $replace = $this->buildRequestReplacements($replace);
        } else {
            $replace = array_merge($replace, [
                'DummyFullRequestClass' => '',
                'DummyRequestClass' => '',
                'DummyRequestVariable' => '',
                'DummyDelimiter' => '',
            ]);
        }

        if ($this->option('service')) {
            $replace = $this->buildServiceReplacements($replace);
        } else {
            $replace = array_merge($replace, [
                'DummyFullServiceClass' => '',
                'DummyServiceClass' => '',
                'DummyServiceVariable' => '',
                'DummyDelimiter' => '',
            ]);
        }

        return str_replace(
            array_keys($replace), array_values($replace), parent::buildClass($name)
        );
    }

    protected function buildRequestReplacements($replace) {
        $request = $this->option('request');

        $requestClass = $this->parseRequest($request);

        if (!class_exists($requestClass)) {
            if ($this->confirm("A {$requestClass} request does not exist. Do you want to generate it?", true)) {
                $this->call("make:request", ['name' => $requestClass]);
            }
        }

        return array_merge($replace, [
            'DummyFullRequestClass' => 'use ' . $requestClass . ';',
            'DummyRequestClass' => class_basename($requestClass),
            'DummyRequestVariable' => '$' . lcfirst(class_basename($requestClass))
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
        $service = $this->option('service');

        $serviceClass = $this->parseService($service);

        if (!class_exists($serviceClass)) {
            if ($this->confirm("A {$serviceClass} service does not exist. Do you want to generate it?", true)) {
                $this->call("hsky:service", ['name' => $serviceClass]);
            }
        }

        return array_merge($replace, [
            'DummyFullServiceClass' => 'use ' . $serviceClass . ';',
            'DummyServiceClass' => class_basename($serviceClass),
            'DummyServiceVariable' => '$' . lcfirst(class_basename($serviceClass))
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
}
