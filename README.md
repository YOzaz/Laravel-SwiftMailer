# Laravel 4 and Swift Mailer integrator

Package, which tries to solve long-term daemon worker issue.
For reference:

* [swiftmailer/swiftmailer#490](https://github.com/swiftmailer/swiftmailer/issues/490)
* [laravel/framework#4573](https://github.com/laravel/framework/issues/4573)

## Installation

Begin by installing this package through Composer. Edit your project's `composer.json` file to require `yozaz/laravel-swiftmailer`.

```json
"require": {
	"yozaz/laravel-swiftmailer": "~1.0"
}
```

Next, update Composer from the Terminal:

```bash
composer update
```

Once this operation completes, the next step is to add the service provider. Open `app/config/app.php`, and add a new item to the providers array.

```php
'YOzaz\LaravelSwiftmailer\ServiceProvider',
```

The final step is to replace Laravel's native Mailer Facade with the one, provided in a package. Open `app/config/app.php`, and replace "Mail" alias with:

```php
'Mail' => 'YOzaz\LaravelSwiftmailer\Facade',
```

That's it! You're all set to go.

## Usage

Package is built in a way, that nothing special needs to be done. It's basically a wrapper, so all `Mailer::send()` and similar functions will work out of the box.

However, real magic starts when emails are sent through [Beanstalkd](https://github.com/kr/beanstalkd) + [Supervisor](http://supervisord.org/) + [Laravel Queue Daemon Worker](http://laravel.com/docs/4.2/queues#daemon-queue-worker) (as an example) architecture. To maintain stable connection, package makes safe-error calls to SwiftMailer transport reset functions before email is being sent. 

**N.B.** This package _does not_ overwrite 'mailer' IoC binding in Laravel for legacy purposes.

### If you're using Facaders

Nothing needs to be changed.

### If you're using IoC binding

New IoC binding can be accessed in following way.

```php
var $mailer = App::make('laravel-swiftmailer.mailer');
```

### If you're instantiating Mailer object manually

Just initialize `\YOzaz\LaravelSwiftmailer\Mailer`. Optionaly you can pass instance of `\Illuminate\Mail\Mailer` to constructor, or set it later on regarding your needs:

```php
// automatic resolving
var $mailer_auto = new \YOzaz\LaravelSwiftmailer\Mailer();
// Explicit instance
var $mailer_explicit = new \YOzaz\LaravelSwiftmailer\Mailer( App::make('mailer') );
// Custom instance
var $mailer_custom = new \YOzaz\LaravelSwiftmailer\Mailer();
$mailer_custom->setMailer( App::make('mailer') );
// Shorter syntax for above
var $mailer_custom = Mailer::setMailer( App::make('mailer') );
```

### If you're initializing Swift_Mailer manually

Well, then this package is not really required for you. Just follow approach in `\YOzaz\LaravelSwiftmailer\Mailer` class - `resetSwiftTransport()` does the trick. Or follow any other solutions which basicaly execute stop() function after every email being sent.

## What about Laravel 5?

Package supports only Laravel 4 only at the moment. However, if you have spare time to adopt it (shouldn't be difficult) - feel free to create a pull request.

## @todo

* Package may throw an error, if "pretending" flag is set.
* ok ok. Laravel 5 support.

## Credits

All credits go to [xdecock](https://github.com/xdecock), author of [Swift Mailer](https://github.com/xdecock/swiftmailer), for providing ready-made solution implemented in this package.

## License

Laravel-SwiftMailer package is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).
