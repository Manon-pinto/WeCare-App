<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\RateLimiter\RateLimiterFactory;

#[AsEventListener(event: KernelEvents::REQUEST, priority: 10)]
class LoginRateLimiterListener
{
    public function __construct(
        #[\Symfony\Component\DependencyInjection\Attribute\Autowire(service: 'limiter.login_brute_force')]
        private RateLimiterFactory $loginBruteForceLimiter,
    ) {}

    public function __invoke(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if ($request->getPathInfo() !== '/api/login' || !$request->isMethod('POST')) {
            return;
        }

        $limiter = $this->loginBruteForceLimiter->create($request->getClientIp());
        $limit = $limiter->consume(1);

        if (!$limit->isAccepted()) {
            $retryAfter = $limit->getRetryAfter()->getTimestamp() - time();
            $event->setResponse(new JsonResponse(
                ['error' => 'Trop de tentatives de connexion. Veuillez réessayer dans ' . ceil($retryAfter / 60) . ' minutes.'],
                429,
                ['Retry-After' => $retryAfter]
            ));
        }
    }
}
