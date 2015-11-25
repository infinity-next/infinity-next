<?php namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class BladeServiceProvider extends ServiceProvider
{
	
	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;
	
	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$blade = $this->app['view']->getEngineResolver()->resolve('blade')->getCompiler();
		
		$blade->extend(function($value, $compiler)
		{
			$value = preg_replace("/@set\('(.*?)'\,(.*)\)/", '<?php $$1 = $2; ?>', $value); 
			return $value;
		});
		
		$blade->directive('ifhas', function($expression)
		{
			if (preg_match("/(?P<name>\w+)/", $expression, $m))
			{
				return "<?php if (array_key_exists(\"{$m['name']}\", app('view')->getSections())) : ?>";
			}
			
			return "<?php if (false) : ?>";
		});
		
		$blade->directive('spaceless', function($expression)
		{
			return "<?php ob_start(); ?>";
		});
		
		$blade->directive('endspaceless', function($expression)
		{
			return "<?php echo trim(preg_replace('/(?<=>)[\s]+(?=<)/', \"\", ob_get_clean())); ?>";
		});
	}
	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}
	
}