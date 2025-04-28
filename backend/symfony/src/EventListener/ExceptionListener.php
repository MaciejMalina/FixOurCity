<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionListener
{
    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        $statusCode = 500;
        $message = 'Internal Server Error';

        if ($exception instanceof AuthenticationException) {
            $statusCode = 401;
            $message = 'Unauthorized - Please log in';
        } elseif ($exception instanceof AccessDeniedHttpException) {
            $statusCode = 403;
            $message = 'Access Denied';
        } elseif ($exception instanceof NotFoundHttpException) {
            $statusCode = 404;
            $message = 'Resource not found';
        } elseif ($exception instanceof ValidationFailedException) {
            $statusCode = 422;
            $message = 'Validation failed';
        } elseif ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
            $message = $exception->getMessage();
        }

        $response = new JsonResponse([
            'error' => $message,
            'status' => $statusCode,
        ], $statusCode);

        $event->setResponse($response);
    }
}
