# Laravel and Swift Mailer integrator [deprecated]

[![Latest Stable Version](https://poser.pugx.org/YOzaz/Laravel-SwiftMailer/v/stable.svg)](https://packagist.org/packages/yozaz/laravel-swiftmailer)
[![Total Downloads](https://poser.pugx.org/YOzaz/Laravel-SwiftMailer/downloads.svg)](https://packagist.org/packages/yozaz/laravel-swiftmailer)
[![License](https://poser.pugx.org/YOzaz/Laravel-SwiftMailer/license.svg)](https://packagist.org/packages/yozaz/laravel-swiftmailer)

## Deprecated

This package is deprecated, as starting from Laravel 5.0 and above, original Mail class automatically reconnects on every message. See commit here: [Force reconnection to fix mailing on daemon queues] (https://github.com/laravel/framework/commit/af8eb1face000f82e5c85e6eb822075fc313cbb9).

It can still be be used for Laravel 4.2. Actually, it won't do any harm on Laravel 5 as well, but will be pointless.

Original docs below.

---

Package, which tries to solve long-term daemon worker issue.
For reference:

* [swiftmailer/swiftmailer#490](https://github.com/swiftmailer/swiftmailer/issues/490)
* [laravel/framework#4573](https://github.com/laravel/framework/issues/4573)

Compatible with Laravel 4th and 5th versions.

## Installation

Begin by installing this package through Composer. Edit your project's `composer.json` file to require `yozaz/laravel-swiftmailer`.

```json
"require": {
	"yozaz/laravel-swiftmailer": "~3.0"
}
```

Next, update Composer from the Terminal:

```bash
composer update
```

Once this operation completes, the next step is to add the service provider. Open `app/config/app.php` (or `config/app.php`), and add a new item to the providers array.

```php
'YOzaz\LaravelSwiftmailer\ServiceProvider',
```

The final step is to replace Laravel's native Mailer Facade with the one, provided in a package. Open `app/config/app.php` (or `config/app.php`), and replace "Mail" alias with:

```php
'Mail' => 'YOzaz\LaravelSwiftmailer\Facade',
```

That's it! You're all set to go.

## About

Package makes error-safe calls to SwiftMailer transport reset functions before email is being sent. In case reset fails - SMTP connection is restarted, thus maintaining it active for whole application living cycle.
This is extremely important for long-living applications. E.g. when emails are sent through [Beanstalkd](https://github.com/kr/beanstalkd) + [Supervisor](http://supervisord.org/) + [Laravel Queue Daemon Worker](http://laravel.com/docs/4.2/queues#daemon-queue-worker) architecture, Laravel application never quits - therefore SMTP connection is kept active and timeouts after some time. Resetting and/or restarting SMTP connection automaticaly solves this problem in general.

**N.B.** While auto-reset feature is great, sometimes it's not a preferred behaviour. Be sure to check your SMTP server configuration before using this package.

## Usage

Package is built in a way, that nothing special needs to be done. It's basically a wrapper, so all `Mailer::send()` and similar functions will work out of the box.

### Auto-reset

Package resets SMTP adapter every time when email is sent (except first call, before SMTP transport is started). You can manipulate this through special helper functions:

```php
// disable auto reset
Mailer::disableAutoReset();
// enable it back
Mailer::enableAutoReset();
// Set my status
Mailer::setAutoReset(true);
// check if auto-reset is enabled
if ( Mailer::autoResetEnabled() ) { ...
```

It is possible to reset SMTP adapter explicitly.

```php
Mailer::reset()->send(...);
```

### Initialization

Package has separate IoC binding. **N.B.** This package _does not_ overwrite 'mailer' IoC binding in Laravel for legacy purposes.

```php
var $mailer = App::make('laravel-swiftmailer.mailer');
```

If you prefer object initialization against Facades, you can instantiate `Mailer` class by yourself, with additional parameters if required. Package will try to instantiate required objects automatically as defaults.

```php
var $mailer = new \YOzaz\LaravelSwiftmailer\Mailer();
```

Optinally, if you have custom wrapper for Laravel's Mailer, or want to manipulate with auto-reset functionality, you can pass additional parameters to IoC binding or class instantiation. Take a look at class constructor for details.

```php
var $my_custom_mailer = App::make('mailer');
// pass custom mailer and disable auto-reset
var $mailer = new \YOzaz\LaravelSwiftmailer\Mailer( $my_custom_mailer, false );
```

### Setting custom mailer instance

To set custom mailer instance, call this method:

```php
Mailer::setMailer( $my_custom_mailer );
```

## Credits

All credits go to [xdecock](https://github.com/xdecock), author of [Swift Mailer](https://github.com/xdecock/swiftmailer), for providing ready-made solution implemented in this package.

## License

Laravel-SwiftMailer package is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).


[![Bitdeli Badge](https://d2weczhvl823v0.cloudfront.net/YOzaz/laravel-swiftmailer/trend.png)](https://bitdeli.com/free "Bitdeli Badge")

