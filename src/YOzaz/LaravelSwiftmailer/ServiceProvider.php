<?php namespace YOzaz\LaravelSwiftmailer;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Flag if this is Laravel 5 instance
	 *
	 * @var bool
	 */
	protected $is_laravel_5 = true;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		if ( !$this->is_laravel_5 )
		{
			$this->package('yozaz/laravel-swiftmailer');
		}
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$app = $this->app ?: app();
		$app_version = method_exists($app, 'version') ? $app->version() : $app::VERSION;

		if ( version_compare( $app_version, '5.0', '<' ) )
		{
			$this->is_laravel_5 = false;
		}

		$this->app->singleton('laravel-swiftmailer.mailer', function($app)
		{
			/** @var \Illuminate\Foundation\Application $app */
			$mailer = new Mailer( $app->make('mailer') );

			if ( $app->bound('queue') )
			{
				$this->is_laravel_5 ? $mailer->setQueue($app['queue.connection']) : $mailer->setQueue($app['queue']);
			}

			return $mailer;
		} );
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return ['laravel-swiftmailer.mailer'];
	}

}
