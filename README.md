# Laravel 4 and Swift Mailer integrator

Package, which tries to solve long-term daemon worker issue.
For reference:

* [swiftmailer/swiftmailer#490](https://github.com/swiftmailer/swiftmailer/issues/490)
* [laravel/framework#4573](https://github.com/laravel/framework/issues/4573)

## Installation

Begin by installing this package through Composer. Edit your project's `composer.json` file to require `yozaz/laravel-swiftmailer`.

	"require": {
		"yozaz/laravel-swiftmailer": "1.*"
	}

Next, update Composer from the Terminal:

	composer update

Once this operation completes, the next step is to add the service provider. Open `app/config/app.php`, and add a new item to the providers array.

	'YOzaz\LaravelSwiftmailer\ServiceProvider',

The final step is to replace Laravel's native Mailer Facade with the one, provided in a package. Open `app/config/app.php`, and replace "Mail" alias with:

	'Mail' => 'YOzaz\LaravelSwiftmailer\Facade',

That's it! You're all set to go.

## Usage

Package is built in a way, that nothing special needs to be done. It's basically a wrapper, so all `Mailer::send()` and similar functions will work out of the box.

However, real magic starts when emails are sent through [Beanstalkd](https://github.com/kr/beanstalkd) + [Supervisor](http://supervisord.org/) + [Laravel Queue Daemon Worker](http://laravel.com/docs/4.2/queues#daemon-queue-worker) (as an example) architecture. To maintain stable connection, package makes safe-error calls to SwiftMailer transport reset functions before email is being sent. 

## What about Laravel 5?

Package supports only Laravel 4 only at the moment. However, if you have spare time to adopt it (shouldn't be difficult) - feel free to create a pull request.

## Credits

All credits go to [xdecock](https://github.com/xdecock), author of [Swift Mailer](https://github.com/xdecock/swiftmailer), for providing ready-made solution implemented in this package.

## License

Laravel-SwiftMailer package is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).
