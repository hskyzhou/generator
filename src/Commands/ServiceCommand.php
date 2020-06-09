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
                                {--model= : model名称}
                                {--exception : 是否生成exception，并且生成create,update,delete异常，目前通过--module设置.}
                                {--module= : exception的模块.}
                                {--skip-migration= : 设置model情况，不生成migration}
                                {--skip-model= : 设置model情况，不生成model}
                                {--skip-model= : 设置model情况，不生成model}
                                {--skip-exception= : 不生成Exception}
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
            $this->setOption('skip-model', true);
            $this->setOption('skip-migration', true);
            $this->setOption('skip-exception', true);
        }

        if ($this->option('model')) {
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
        if (!$this->option('model')) {
            if ($this->confirm("是否需要生成对应的model,migration,repository", true)) {
                $this->input->setOption('model', $this->ask('输入对应的model'));
            }
        }

        if (!$this->option('exception')) {
            if ($this->confirm("是否设置exception", true)) {
                $this->input->setOption('exception', true);
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

        if ($this->option('exception')) {
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
        $module = $this->option('module');

        if (!$module) {
            if ($this->confirm("你需要设置exception的目录吗", true)) {
                $module = $this->ask("请输入exception目录名称");
            }
        }
        $names = [
            'CreateFailException',
            'UpdateFailException',
            'DeleteFailException',
        ];

        $namespace = $module ? 'App\\Exceptions\\' . $module . '\\' : 'App\\Exceptions\\';

        foreach ($names as $name) {
            $exceptionName = $module ? $module . '/' . $name : $name;
            $this->call('hsky:exception', [
                'name' => $exceptionName, 
                '--code' => str_replace('Exception', 'Code', $name),
                '--message' => str_replace('Exception', 'Message', $name),
            ]);

            $replace = array_merge($replace, [
                'DummyFull'.$name => $namespace . $name . ' as ' . ucfirst($module . $name),
                'Dummy'.$name => ucfirst($module . $name),
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
}
