<?php

namespace App\Twig\Runtime;

use Twig\Extension\RuntimeExtensionInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use App\DTO\Breadcrumb\Link;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class BreadcrumbRuntime implements RuntimeExtensionInterface
{
    public function __construct(protected RequestStack $requestStack, protected UrlGeneratorInterface $urlGenerator)
    {
        // Inject dependencies if needed
    }

    public function breadcrumb(?array $configured = []): array
    {
        $routes = [];

        $request = $this->requestStack->getCurrentRequest();

        $routeName = $request->attributes->get('_route');

        $routes = [
            new Link(label: 'Home', route: 'app_home', isCurrent: $routeName === 'app_home'),
        ];

        foreach ($configured as $route) {
            dump($route);
            $routes[] = new Link(
                label: $route['label'],
                route: $this->urlGenerator->generate($route['route'], $route['parameters'] ?? []),
                isCurrent: $routeName === $route['route'],
                routeParameters: $route['parameters'] ?? []
            );
        }

        return $routes;
    }
}
