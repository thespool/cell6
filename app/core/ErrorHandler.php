<?php

namespace Core;

/**
 * Class ErrorHandler
 * @package Core
 */
class ErrorHandler {

    /**
     *
     * @var array Define for what type of errors to call exception handler in shutdown function
     */
    private $shutdownErrors = [E_PARSE, E_ERROR, E_USER_ERROR];

    /**
     *
     * @var array List of errors that will be processed by standard PHP error handler
     */
    private $disallowedErrors = [E_NOTICE];

    /**
     * Register all necessary handlers to catch all possible error types
     * @param bool $debugMode
     */
    public function register($debugMode = true) {
        if ($debugMode) {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
            set_exception_handler([$this, 'exceptionHandler']);
            set_error_handler([$this, 'errorHandler']);

            // Register shutdown function to catch fatal errors
            register_shutdown_function([$this, 'shutdownHandler']);
        } else {
            error_reporting(0);
            ini_set('display_errors', '0');

            // TODO: log exceptions to file
        }
    }

    /**
     * Exception handler
     *
     * Called on uncaught exception
     *
     * @param Exception Uncaught exception
     * @return bool
     */
    public function exceptionHandler($e) {
        $this->renderException($e);
        return true;
    }

    /**
     * Error handler
     *
     * Called when error happens.
     *
     * @param $code int Error type (E_ERROR, E_NOTICE etc.)
     * @param $error string Error message
     * @param $file string Path to file where error occurs
     * @param $line int Line number
     * @return bool
     * @throws \ErrorException
     */
    public function errorHandler($code, $error, $file = null, $line = null) {
        if (error_reporting() && !in_array($code, $this->disallowedErrors)) {
            throw new \ErrorException($error, $code, 0, $file, $line);
        }

        return false;
    }

    /**
     * Shutdown function
     *
     * Purpose is to catch fatal errors because it is not possible using
     * error handler.
     */
    public function shutdownHandler() {
        $error = error_get_last();

        if (error_reporting() && $error && in_array($error['type'], $this->shutdownErrors)) {
            ob_clean();
            $this->renderException(new \ErrorException($error['message'], $error['type'], 0, $error['file'], $error['line']));
        }

        exit(1);
    }

    /**
     * @param $exception
     */
    private function renderException($exception) {
        header("HTTP/1.0 500 Internal Server Error");
        $out = <<< EOD
<html>
	<head>
		<meta charset="utf-8" />
		<title>Error</title>
		<style type="text/css">
			body { font-family: "Consolas", Lucida Console, Courier; font-size: 14px; margin: 0; padding: 0 }
			.error-message { padding: 11px 22px; background-color: #fdf5ce; }
			.error-message h1 { font-size: 31px; color: #d52627 }
			.error-message h2 { font-weight: normal; font-size: 19px }
			.trace { margin: 11px 22px }
			.trace pre { padding: 6px 11px; border: 1px solid #ddd }
		</style>
	</head>
	<body>
		<div class="error-message">
			<h1>ERROR {$exception->getCode()}: {$exception->getMessage()}</h1>
			<h2>File {$exception->getFile()} on line {$exception->getLine()}.</h2>
		</div>
		<div class="trace">
			<h3>Trace</h3>
			<pre>{$exception->getTraceAsString()}</pre>
		</div>
	</body>
</html>
EOD;
        echo $out;
    }

}
