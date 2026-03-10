<?php
// src/Form/Extension/PhoneFormatTypeExtension.php

namespace App\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class PhoneFormatTypeExtension extends AbstractTypeExtension
{
    public static function getExtendedTypes(): iterable
    {
        return [FormType::class];
    }

    /**
     * Bubble up a flag on the root view when any child has the phoneFormat class.
     */
    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        // Only process root forms to avoid redundant checks on children
        if ($view->parent !== null) {
            return;
        }

        $view->vars['needs_phone_format'] = $this->hasPhoneFormatField($view);
    }

    /**
     * Recursively check whether any field in the form tree carries the phoneFormat class.
     */
    private function hasPhoneFormatField(FormView $view): bool
    {
        $classes = $view->vars['attr']['class'] ?? '';
        if (in_array('phoneFormat', explode(' ', $classes), true)) {
            return true;
        }

        foreach ($view->children as $child) {
            if ($this->hasPhoneFormatField($child)) {
                return true;
            }
        }

        return false;
    }
}