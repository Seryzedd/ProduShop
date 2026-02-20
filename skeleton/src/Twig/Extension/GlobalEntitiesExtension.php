<?php

namespace App\Twig\Extension;

use App\Twig\Runtime\GlobalEntitiesExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\Extension\GlobalsInterface;
use App\Service\Cart\CartService;
use App\Repository;

class GlobalEntitiesExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(private Repository\Product\ShelfRepository $shelfRepo, private CartService $cartService)
    {

    }

    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/3.x/advanced.html#automatic-escaping
            new TwigFilter('filter_name', [GlobalEntitiesExtensionRuntime::class, 'doSomething']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('function_name', [GlobalEntitiesExtensionRuntime::class, 'doSomething']),
        ];
    }

    public function getGlobals(): array
    {
        return [
            'shelvesEntities' => $this->shelfRepo->findAll(),
            'cart' => $this->cartService,
        ];
    }
}
