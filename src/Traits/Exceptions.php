<?php

namespace Zdirnecamlcs96\Helpers\Traits;

use Throwable;
use Illuminate\Http\Request;
use Psy\Exception\Exception;
use Psy\Exception\FatalErrorException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

trait Exceptions
{
    use Requests;

    private function __throwException($e)
    {
        if (is_a($e, Exception::class))
        throw new $e;
    }

    private function __beautifyResponse(Request $request, Throwable $e)
    {
        $json = $this->__expectsJson() && optional($request->route())->getName() != 'password.update';

        if(is_a($e, TokenMismatchException::class)) {
            return $json
                ? $this->__apiFailed($e->getMessage(), null, 419)
                : abort(419);
        }
        if (is_a($e, MethodNotAllowedHttpException::class)) {
            return $json
                ? $this->__apiMethodNotAllowed($e->getMessage())
                : abort(500);
        }
        if (is_a($e, NotFoundHttpException::class)) {
            if ($json) {
                if ($e->getPrevious() && is_a($e->getPrevious(), ModelNotFoundException::class)){
                    return $this->__apiNotFound("Record Not Found.");
                }
                return $this->__apiNotFound("404 Not Found.");
            }
        }

        if (is_a($e, ModelNotFoundException::class)) {
            if ($json) {
                return $this->__apiNotFound("Record Not Found.");
            }
        }

        if (is_a($e, ThrottleRequestsException::class)) {
            if ($json) {
                return $this->__apiFailed("Server Timeout. Please try again later.", null, 429);
            }
        }

        if (is_a($e, FatalErrorException::class)) {
            if ($json) {
                return $this->__apiFailed("Something went wrong.", null, 500);
            }
        }

        return false;
    }

    /**
     * Throw Record not found exception
     *
     * @return \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function __throwModelNotFound()
    {
        $this->__throwException(ModelNotFoundException::class);
    }
}