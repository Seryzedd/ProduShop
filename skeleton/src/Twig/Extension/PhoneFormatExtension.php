<?php

namespace App\Twig\Extension;

use Symfony\Component\Asset\Packages;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class PhoneFormatExtension extends AbstractExtension
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly Packages $packages,
    ) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'phone_format_scripts',
                $this->renderScripts(...),
                ['is_safe' => ['html']]
            ),
        ];
    }

    public function renderScripts(): string
    {
        $messages = json_encode([
            'required' => $this->translator->trans('phone.required'),
            'invalid'  => $this->translator->trans('phone.invalid'),
        ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR);

        $jsUrl = $this->packages->getUrl('script/phoneValidator.js');

        return <<<HTML
            <script>window.phoneValidation = { messages: {$messages} };</script>
            <script src="{$jsUrl}" defer></script>
            HTML;
    }
}