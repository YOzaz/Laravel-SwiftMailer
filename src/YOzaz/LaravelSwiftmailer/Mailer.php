<?php namespace YOzaz\LaravelSwiftmailer;

use Closure;
use Exception;
use Illuminate\Support\Str;

class Mailer {

	/**
	 * Original Mailer instance
	 *
	 * @var \Illuminate\Mail\Mailer
	 */
	protected $mailer;

	/**
	 * Original QueueManager instance.
	 *
	 * @var \Illuminate\Queue\QueueManager
	 */
	protected $queue;

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
	 * Sets custom mailer
	 *
	 * @param \Illuminate\Queue\QueueManager $queue
	 * @return Mailer
	 */
	public function setQueue( $queue )
	{
		$this->queue = $queue;

		if ( $this->mailer )
		{
			$this->mailer->setQueue( $queue );
		}

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
		if ( $this->mailer && method_exists( $this->mailer, 'isPretending' ) && $this->mailer->isPretending() )
		{
			return;
		}

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
	 * Build the callable for a queued e-mail job.
	 *
	 * @param  mixed  $callback
	 * @return mixed
	 */
	protected function buildQueueCallable($callback)
	{
		if ( !$callback instanceof Closure )
		{
			return $callback;
		}

		if ( class_exists('\SuperClosure\Serializer') )
		{
			return (new \SuperClosure\Serializer)->serialize($callback);
		}
		else
		{
			return serialize(new \Illuminate\Support\SerializableClosure($callback));
		}
	}

	/**
	 * Get the true callable for a queued e-mail message.
	 *
	 * @param  array  $data
	 * @return mixed
	 */
	protected function getQueuedCallable(array $data)
	{

		if (Str::contains($data['callback'], 'SerializableClosure'))
		{
			if ( class_exists('\SuperClosure\Serializer') )
			{
				return (new \SuperClosure\Serializer)->unserialize($data['callback']);
			}
			else
			{
				return with(unserialize($data['callback']))->getClosure();
			}
		}

		return $data['callback'];
	}

	/**
	 * Queue a new e-mail message for sending.
	 *
	 * @param  string|array  $view
	 * @param  array   $data
	 * @param  \Closure|string  $callback
	 * @param  string  $queue
	 * @return mixed
	 */
	public function queue($view, array $data, $callback, $queue = null)
	{
		$callback = $this->buildQueueCallable($callback);

		return $this->queue->push('laravel-swiftmailer.mailer@handleQueuedMessage', compact('view', 'data', 'callback'), $queue);
	}

	/**
	 * Queue a new e-mail message for sending on the given queue.
	 *
	 * @param  string  $queue
	 * @param  string|array  $view
	 * @param  array  $data
	 * @param  \Closure|string  $callback
	 * @return mixed
	 */
	public function onQueue($queue, $view, array $data, $callback)
	{
		return $this->queue($view, $data, $callback, $queue);
	}

	/**
	 * Queue a new e-mail message for sending on the given queue.
	 *
	 * This method didn't match rest of framework's "onQueue" phrasing. Added "onQueue".
	 *
	 * @param  string  $queue
	 * @param  string|array  $view
	 * @param  array  $data
	 * @param  \Closure|string  $callback
	 * @return mixed
	 */
	public function queueOn($queue, $view, array $data, $callback)
	{
		return $this->onQueue($queue, $view, $data, $callback);
	}

	/**
	 * Queue a new e-mail message for sending after (n) seconds.
	 *
	 * @param  int  $delay
	 * @param  string|array  $view
	 * @param  array  $data
	 * @param  \Closure|string  $callback
	 * @param  string  $queue
	 * @return mixed
	 */
	public function later($delay, $view, array $data, $callback, $queue = null)
	{
		$callback = $this->buildQueueCallable($callback);

		return $this->queue->later($delay, 'laravel-swiftmailer.mailer@handleQueuedMessage', compact('view', 'data', 'callback'), $queue);
	}

	/**
	 * Queue a new e-mail message for sending after (n) seconds on the given queue.
	 *
	 * @param  string  $queue
	 * @param  int  $delay
	 * @param  string|array  $view
	 * @param  array  $data
	 * @param  \Closure|string  $callback
	 * @return mixed
	 */
	public function laterOn($queue, $delay, $view, array $data, $callback)
	{
		return $this->later($delay, $view, $data, $callback, $queue);
	}

	/**
	 * Handle a queued e-mail message job.
	 *
	 * @param  \Illuminate\Queue\Jobs\Job  $job
	 * @param  array  $data
	 * @return void
	 */
	public function handleQueuedMessage($job, $data)
	{
		$this->send($data['view'], $data['data'], $this->getQueuedCallable($data));

		$job->delete();
	}

	/**
	 * In case we are accessing Mailer specific functions, we can pass it to parent class to take care of
	 * Sending methods will be intercepted and transport will be reset if required
	 *
	 * @param $method
	 * @param array $args
	 * @return mixed
	 */
	public function __call( $method, array $args )
	{
		$intercepted_methods = array(
			'raw',
			'plain',
			'send',
		);

		if ( $this->autoResetEnabled() && in_array($method, $intercepted_methods) )
		{
			$this->resetSwiftTransport();
		}

		return call_user_func_array( [$this->mailer, $method], $args );
	}
}
