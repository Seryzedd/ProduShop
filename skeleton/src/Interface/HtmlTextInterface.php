<?php

namespace App\Interface;

/**
 * An interface that the invoice Subject object should implement.
 * In most circumstances, only a single object should implement
 * this interface as the ResolveTargetEntityListener can only
 * change the target to a single object.
 */
interface HtmlTextInterface
{
    // List any additional methods that your InvoiceBundle
    // will need to access on the subject so that you can
    // be sure that you have access to those methods.

    public function getHtml(): string;
}