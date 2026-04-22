<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;

class AuthSubscriber implements EventSubscriberInterface
{
    private const WHITELIST = ['login', 'login_check', 'logout', '_wdt', '_profiler', '_error'];

    public function __construct(private RouterInterface $router) {}

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => ['onRequest', 10]];
    }

    public function onRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $route   = $request->attributes->get('_route', '');

        foreach (self::WHITELIST as $prefix) {
            if (str_starts_with($route, $prefix)) {
                return;
            }
        }

        if (!$request->getSession()->get('user_id')) {
            $event->setResponse(new RedirectResponse($this->router->generate('login')));
        }
    }
}
