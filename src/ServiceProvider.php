<?php 

namespace HskyZhou\Generator;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
	/**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;
    /**
     *
     * @return void
     */
    public function boot()
    {
    	// $this->publishes([__DIR__ . '/../config/hskygenerator.php' => config_path('hskygenerator.php')], 'config');
        
        // $this->mergeConfigFrom(__DIR__ . '/../config/hskygenerator.php', 'hskygenerator');
    }
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
       $this->commands('HskyZhou\Generator\ControllerCommand');
       $this->commands('HskyZhou\Generator\ServiceCommand');
       $this->commands('HskyZhou\Generator\ExceptionCommand');
       $this->commands('HskyZhou\Generator\ErrorCodeCommand');
       $this->commands('HskyZhou\Generator\TraitCommand');
    }
    
    /**ControllerCommand
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}