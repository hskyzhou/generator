<?php 

namespace HskyZhou\Generator;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Traitor\Traitor;

class TraitCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hsky:trait {name} {--service=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '自动生成trait文件';

    protected $type = 'trait';

    protected function getStub()
    {
        return __DIR__ . '/stubs/trait.stub';
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        parent::handle();

    	if ($this->option('service')) {
            $services = explode(',', $this->option('service'));
            foreach ($services as $service) {
                $trait = $this->qualifyClass($this->getNameInput());

                $class = $this->qualifyClass($this->getServiceNameInput($service));

                Traitor::addTrait($trait)->toClass($class);
            }
        }
    }

    public function getNameInput()
    {
        return config('hskygenerator.path.traits') . '/' . $this->argument('name');
    }

    public function getServiceNameInput($service)
    {
        return config('hskygenerator.path.services') . '/' . $service;
    }
}
