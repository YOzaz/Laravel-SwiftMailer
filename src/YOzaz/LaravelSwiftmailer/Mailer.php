<?php namespace YOzaz\LaravelSwiftmailer;

use Exception;
use Swift_Transport_AbstractSmtpTransport;
use Illuminate\Mail\Mailer as BaseMailer;

class Mailer {

	/**
	 * Original Mailer instance
	 *
	 * @var \Illuminate\Mail\Mailer
	 */
	protected $mailer;

	/**
	 * Create a new SwiftMailer instance.
	 *
	 * @param \Illuminate\Mail\Mailer $mailer
	 * @return Mailer
	 */
	public function __construct( BaseMailer $mailer = null )
	{
		// dirty check if we're in Laravel
		if ( !isset($mailer) && function_exists('app') )
		{
			$mailer = app('mailer');
		}

		$this->mailer = $mailer ?: null;
	}

	/**
	 * Sets custom mailer
	 *
	 * @param \Illuminate\Mail\Mailer $mailer
	 * @return Mailer
	 */
	public function setMailer( BaseMailer $mailer )
	{
		$this->mailer = $mailer;

		return $this;
	}

	/**
	 * Get the Swift Mailer Transport instance.
	 *
	 * @return \Swift_Transport
	 */
	protected function getSwiftMailerTransport()
	{
		return $this->mailer->getSwiftMailer()->getTransport();
	}

	/**
	 * Reset Swift Mailer SMTP transport adapter
	 *
	 * @return void
	 */
	protected function resetSwiftTransport()
	{
		if ( ! $transport = $this->getSwiftMailerTransport())
		{
			return;
		}

		try
		{
			// Send RESET to restart the SMTP status and check if it's ready for running
			if ($transport instanceof Swift_Transport_AbstractSmtpTransport)
			{
				$transport->reset();
			}
		}
		catch (Exception $e)
		{
			// In case of failure - let's try to restart it
			try
			{
				$transport->stop();
			}
			catch (Exception $e)
			{
				// Just start it then...
			}

			$transport->start();
		}
	}

	/**
	 * Send a new message using a view.
	 *
	 * @param  string|array  $view
	 * @param  array  $data
	 * @param  \Closure|string  $callback
	 * @return mixed
	 */
	public function send($view, array $data, $callback)
	{
		$this->resetSwiftTransport();

		return $this->mailer->send($view, $data, $callback);
	}

	/**
	 * In case we are accessing Mailer specific functions, we can pass it to parent class to take care of
	 *
	 * @param $method
	 * @param array $args
	 * @return mixed
	 */
	public function __call( $method, array $args )
	{
		return call_user_func_array( [$this->mailer, $method], $args );
	}
}
