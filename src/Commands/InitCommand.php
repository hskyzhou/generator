<?php 

namespace HskyZhou\Generator;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class InitCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hsky:init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '生成Service';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Init Service';

    protected $stub;
    protected $nameInput;
    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        // return __DIR__.'/stubs/init.stub';
        return $this->stub;
    }

    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getNameInput()
    {
        // return 'App\Services\Service';
        return $this->nameInput;
    }

    public function handle()
    {
        /*init service*/
        $this->stub = __DIR__.'/stubs/init.stub';
        $this->nameInput = config('hskygenerator.path.services') . '/Service';
        $this->type = 'init Service';
        parent::handle();

        /*init trait*/
        $this->stub = __DIR__.'/stubs/resulttrait.stub';
        $this->nameInput = config('hskygenerator.path.traits') . '/ResultTrait';
        $this->type = 'init ResultTrait';
        parent::handle();

        $this->stub = __DIR__.'/stubs/servicetrait.stub';
        $this->nameInput = config('hskygenerator.path.traits') . '/ServiceTrait';
        $this->type = 'init ServiceTrait';
        parent::handle();

    }
}
