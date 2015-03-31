<?php namespace YOzaz\LaravelSwiftmailer;

use Exception;

class Mailer {

	/**
	 * Original Mailer instance
	 *
	 * @var \Illuminate\Mail\Mailer
	 */
	protected $mailer;

	/**
	 * Flag if auto-reset is enabled
	 *
	 * @var boolean
	 */
	protected $auto_reset;

	const AUTO_RESET_ENABLED = true;
	const AUTO_RESET_DISABLED = false;

	/**
	 * Create a new Mailer instance.
	 *
	 * @param object $mailer
	 * @param bool $enable_auto_reset
	 */
	public function __construct( $mailer = null, $enable_auto_reset = self::AUTO_RESET_ENABLED )
	{
		// dirty check if we're in Laravel
		if ( ! $mailer && function_exists('app') )
		{
			$mailer = app('mailer');
		}

		$this->setMailer( $mailer );
		$this->setAutoReset( $enable_auto_reset );
	}

	/**
	 * Sets custom mailer
	 *
	 * @param object $mailer
	 * @return Mailer
	 */
	public function setMailer( $mailer )
	{
		$this->mailer = $mailer;

		return $this;
	}

	/**
	 * Sets flag for auto reset
	 *
	 * @param bool $status
	 * @return Mailer
	 */
	protected function setAutoReset( $status )
	{
		$this->auto_reset = $status;

		return $this;
	}

	/**
	 * Enables auto reset
	 *
	 * @return Mailer
	 */
	public function enableAutoReset()
	{
		return $this->setAutoReset( self::AUTO_RESET_ENABLED );
	}

	/**
	 * Enables auto reset
	 *
	 * @return Mailer
	 */
	public function disableAutoReset()
	{
		return $this->setAutoReset( self::AUTO_RESET_DISABLED );
	}

	/**
	 * Enables auto reset
	 *
	 * @return Mailer
	 */
	public function autoResetEnabled()
	{
		return $this->auto_reset === self::AUTO_RESET_ENABLED;
	}

	/**
	 * Get the Swift Mailer Transport instance.
	 *
	 * @return \Swift_Transport|null
	 */
	protected function getSwiftMailerTransport()
	{
		if ( ! $mailer = $this->mailer )
		{
			return null;
		}

		if ( ! $swift_mailer = $mailer->getSwiftMailer() )
		{
			return null;
		}

		if ( ! is_a( $swift_mailer, '\Swift_Mailer' ) )
		{
			return null;
		}

		return $swift_mailer->getTransport();
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

		if ( ! is_a( $transport, '\Swift_Transport_AbstractSmtpTransport' ) )
		{
			return;
		}

		if ( ! $transport->isStarted() )
		{
			$transport->start();

			return;
		}

		try
		{
			// Send RESET to restart the SMTP status and check if it's ready for running
			$transport->reset();
		}
		catch (Exception $e)
		{
			// In case of failure - let's try to stop it
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
	 * Manual reset for SMTP adapter
	 *
	 * @return Mailer
	 */
	public function reset()
	{
		$this->resetSwiftTransport();

		return $this;
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
		if ( $this->autoResetEnabled() )
		{
			$this->resetSwiftTransport();
		}

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
