<?php 

namespace HskyZhou\Generator;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Illuminate\Support\Str;

class ServiceCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hsky:service
                                {name}
                                {--m|model= : model名称}
                                {--skip-migration : 设置model情况，不生成migration}
                                {--skip-model : 设置model情况，不生成model}
                                {--skip-exception : 不生成Exception}
                                {--skip-all= : 只生成service本身}
                            ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '自动生成service文件';

    protected $type = 'service';

    protected function getStub()
    {
        if ($this->option('model')) {
            return __DIR__ . '/stubs/service.curd.stub';
        }
        return __DIR__ . '/stubs/service.stub';
    }

    public function handle()
    {
        // 屏蔽其余生成文件
        if ($this->option('skip-all')) {
            $this->input->setOption('skip-model', true);
            $this->input->setOption('skip-migration', true);
            $this->input->setOption('skip-exception', true);
        }

        if (!$this->option('skip-model')) {
            // 验证是否安装有repository
            if (!config('repository')) {
                $this->error("请先安装l5repositoory; composer require prettus/l5-repository");
                return false;
            }
        }

        parent::handle();
    }

    protected function buildClass($name)
    {
        if (!$this->option('skip-model')) {
            if (!$this->option('model') && $model = $this->ask('输入repository对应的model')) {
                $this->input->setOption('model', $model);
            }
        }

        $replace = [];
        if ($this->option('model')) {
            $model = $this->option('model');
            list($repositoryClass, $interfaceClass) = $this->parseRepository($model);

            if (!class_exists($repositoryClass)) {
                // 创建仓库
                $this->call('make:repository', [
                    'name'        => $this->option('model'),
                    '--skip-migration' => $this->option('skip-migration'),
                    '--skip-model' => $this->option('skip-model'),
                ]);

                // 创建绑定
                $this->call('make:bindings', [
                    'name'    => $this->option('model'),
                ]);
            }

            $replace = $this->buildRepositoryReplacements($replace);

        }

        if (!$this->option('skip-exception')) {
            $replace = $this->buildExceptionReplacements($replace);
        }

        return str_replace(
            array_keys($replace), array_values($replace), parent::buildClass($name)
        );
    }

    protected function buildRepositoryReplacements($replace)
    {
        $model = $this->option('model');
        list($repositoryClass, $interfaceClass) = $this->parseRepository($model);

        return array_merge($replace, [
            'DummyFullInterfaceClass' => $interfaceClass,
            'DummyInterfaceClass' => class_basename($interfaceClass),
            'DummpyRepositoryVariable' => lcfirst(class_basename($interfaceClass)),
        ]);
    }

    protected function parseRepository($model)
    {
        $repositoryNamespace = config('repository.generator.rootNamespace') . config('repository.generator.paths.repositories') . '\\';
        $interfaceNamespace = config('repository.generator.rootNamespace') . config('repository.generator.paths.interfaces') . '\\';

        $model = trim(str_replace('/', '\\', $model), '\\');

        $repository = $model;
        if (! Str::startsWith($model, $repositoryNamespace)) {
            $repository = $repositoryNamespace . $model . 'RepositoryEloquent';
        }

        $interface = $model;
        if (! Str::startsWith($model, $interfaceNamespace)) {
            $interface = $interfaceNamespace . $model . 'Repository';
        }

        return [
            $repository,
            $interface,
        ];
    }

    protected function buildExceptionReplacements($replace)
    {
        $module = basename(str_replace('\\', '/', $this->argument('name')));

        $names = [
            'CreateFail',
            'UpdateFail',
            'DeleteFail',
        ];

        $namespace = 'App\\Exceptions\\' . $module . '\\';

        foreach ($names as $name) {
            $exceptionName = $module . '/' . $name;

            $this->call('hsky:exception', [
                'name' => $exceptionName
            ]);

            $replace = array_merge($replace, [
                'DummyFull'.$name => $namespace . $name . ' as ' . ucfirst($module . $name) . 'Exception',
                'Dummy'.$name => ucfirst($module . $name) . 'Exception',
            ]);
        }

        return $replace;
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Services';
    }

    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getNameInput()
    {
        return trim($this->argument('name') . 'Service');
    }
}
