<?php namespace Rookwood\Turnstile;

use Illuminate\Support\ServiceProvider;
use Config;
use App;

class TurnstileServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		// $this->package moved to register because the service provider needs access to its own package config
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
        $this->package('rookwood/turnstile');

        $policyProviderClass = $this->getPolicyProviderClass();

        $this->app->singleton('policy', function() use($policyProviderClass)
        {
            return $policyProviderClass::make();
        });
	}

    /**
     * Build the policy provider class based on configuration settings
     *
     * @return string
     */
    public function getPolicyProviderClass()
    {
        $policyProviderNamespace = Config::get('turnstile::namespace');

       // Add trailing \ if needed
        if ( ! substr($policyProviderNamespace, -1) == "\\")
        {
            $policyProviderNamespace .= "\\";
        }

        $policyProviderClass = $policyProviderNamespace . "PolicyProvider";

        return $policyProviderClass;
    }

}
