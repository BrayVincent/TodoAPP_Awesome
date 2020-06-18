<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class Redirect404ToHomepageListener
{
    /**
     * @var UrlGeneratorInterface
     */
    private $router;

    /**
     * @var UrlGeneratorInterface $router
     */
    public function __construct(UrlGeneratorInterface $router)
    {
        $this->router = $router;
    }
    
    /**
     * @var ExceptionEvent $event
     * @return null
     */
    public function onKernelException(ExceptionEvent $event)
    {
        // You get the exception object from the received event
        $exception = $event->getThrowable();
        dd($exception);
        // If HttpNotFoundException
        if ($exception instanceof HttpExceptionInterface) {
            // Create redirect response with url for the home page
            $response = new RedirectResponse($this->router->generate('home'));
        }

        // Set the response to be processed
        $event->setResponse($response);
    }
}