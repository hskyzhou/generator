<?php 

namespace HskyZhou\Generator;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Illuminate\Support\Str;

class ExceptionCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hsky:exception 
                                {name}
                                {--code= : code的值}
                                {--message= : message的值}
                            ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '自动生成exception文件';

    protected $type = 'Exception';

    protected function getStub()
    {
        return __DIR__ . '/stubs/exception.stub';
    }

    protected function buildClass($name)
    {
        $errorcode = 'App\\Exceptions\\ErrorCode';

        if (!class_exists($errorcode)) {
            $this->call('hsky:errorcode', ['name' => $errorcode]);
        }

        $codeVariable = $this->option('code') ? $this->option('code') : $this->argument('name') . 'Code';
        $messageVariable = $this->option('message') ? $this->option('message') : $this->argument('name') . 'Message';

        $replace = [
            'DummyCode' => strtoupper(Str::snake(str_replace('/', '', $codeVariable))),
            'DummyMessage' => strtoupper(Str::snake(str_replace('/', '', $messageVariable))),
        ];

        return str_replace(
            array_keys($replace), array_values($replace), parent::buildClass($name)
        );
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Exceptions';
    }
}