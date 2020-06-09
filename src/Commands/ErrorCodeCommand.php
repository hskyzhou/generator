<?php 

namespace HskyZhou\Generator;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ErrorCodeCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hsky:errorcode {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '自动生成ErrorCode文件';

    protected $type = 'ErrorCode';

    protected function getStub()
    {
        return __DIR__ . '/stubs/errorcode.stub';
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
