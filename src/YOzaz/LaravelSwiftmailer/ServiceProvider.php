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
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$app = $this->app;

		if ( version_compare( $app::VERSION, '5.0', '<' ) )
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
		$this->app->singleton('laravel-swiftmailer.mailer', function($app)
		{
			$mailer = new Mailer( $app->make('mailer') );

			if ( $app->bound('queue') )
			{
				$mailer->setQueue($app['queue']);
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
