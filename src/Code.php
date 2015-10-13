<?php
namespace code;

use Exception;
use InvalidArgumentException;

class Code
{
    /**
     * Executes a callable until a timeout is reached or the callable returns `true`.
     *
     * @param  Callable $callable The callable to execute.
     * @param  integer  $timeout  The timeout value.
     * @return mixed
     */
    public static function run($callable, $timeout = 0, $ignoreException = false)
    {
        if (!is_callable($callable)) {
            throw new InvalidArgumentException();
        }

        $timeout = (integer) $timeout;

        pcntl_signal(SIGALRM, function($signal) use ($timeout) {
            throw new TimeoutException("Timeout reached, execution aborted after {$timeout} second(s).");
        }, true);

        pcntl_alarm($timeout);

        $result = null;

        try {
            $result = $callable();
        } catch (TimeoutException $e) {
            throw $e;
        } catch (Exception $e) {
            if (!$ignoreException) {
                throw $e;
            }
        } finally {
            pcntl_alarm(0);
        }

        return $result;
    }

    /**
     * Executes a callable in a loop until a timeout is reached or the callable returns `true`.
     *
     * @param  Callable $callable The callable to execute.
     * @param  integer  $timeout  The timeout value.
     * @return mixed
     */
    public static function spin($callable, $timeout = 0, $ignoreException = false)
    {
        if (!is_callable($callable)) {
            throw new InvalidArgumentException();
        }

        $closure = function() use ($callable, $timeout) {

            $timeout = (float) $timeout;
            $result = false;
            $start = microtime(true);

            do {
                if ($result = $callable()) {
                    return $result;
                }
                $current = microtime(true);

            } while ($current - $start < $timeout);

            throw new TimeoutException("Timeout reached, execution aborted after {$timeout} second(s).");
        };

        return static::run($closure, $timeout, $ignoreException);
    }
}
