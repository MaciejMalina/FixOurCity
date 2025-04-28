<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Http\HttpExceptionInterface;

class ExceptionListener
{
    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        $responseData = [
            'error' => $exception->getMessage(),
        ];

        $statusCode = 500; // Domyślnie Internal Server Error

        if ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
        }

        $responseData['status'] = $statusCode;

        $response = new JsonResponse($responseData, $statusCode);

        $event->setResponse($response);
    }
}
