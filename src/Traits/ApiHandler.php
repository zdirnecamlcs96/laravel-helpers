<?php

namespace Zdirnecamlcs96\Helpers\Traits;

use Throwable;

trait ApiHandler
{

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {

        $json = $this->__expectsJson() && $request->route()->getName() != 'password.update';

        if(is_a($exception, TokenMismatchException::class)) {
            return $json
                ? $this->__apiFailed($exception->getMessage(), null, 419)
                : abort(419);
        }
        if (is_a($exception, MethodNotAllowedHttpException::class)) {
            return $json
                ? $this->__apiMethodNotAllowed($exception->getMessage())
                : abort(500);
        }
        if (is_a($exception, NotFoundHttpException::class)) {
            if ($json) {
                return $this->__apiNotFound("404 Not Found.");
            }
        }

        if (is_a($exception, ModelNotFoundException::class)) {
            if ($json) {
                return $this->__apiNotFound("Model Not Found.");
            }
        }

        if (is_a($exception, ThrottleRequestsException::class)) {
            if ($json) {
                return $this->__apiFailed("Server Timeout. Please try again later.", null, 429);
            }
        }

        if (is_a($exception, FatalErrorException::class)) {
            if ($json) {
                return $this->__apiFailed("Something went wrong.", null, 500);
            }
        }

        return parent::render($request, $exception);
    }
}