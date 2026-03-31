<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Translation\LocaleSwitcher;

final class LocalManagerListener
{
    public function __construct(private LocaleSwitcher $switcher) {}

    #[AsEventListener]
    public function onRequestEvent(RequestEvent $event): void
    {

        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        $session = $request->getSession();

        $requestedLocal = $request->request->get('locale_language');

        if($requestedLocal) {
            $session->set('__current_language', $requestedLocal);
        }

        if($session->get('__current_language')) {
            $locale = $session->get('__current_language');
        } else {
            $locale = $request->getDefaultLocale();
        }

        $request->setLocale($locale);

        $this->switcher->setLocale($locale);  
    }
}
