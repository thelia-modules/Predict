<?php

declare(strict_types=1);

/*************************************************************************************/
/*      This file is part of the Thelia package.                                     */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : dev@thelia.net                                                       */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE.txt  */
/*************************************************************************************/

namespace Predict\Controller\Traits;

use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Thelia\Form\BaseForm;

/**
 * Surfaces a back-office form error to the admin.
 *
 * ParserContext alone is not enough on the Thelia 3 default-twig back-office: every action here
 * redirects after handling the form, and ParserContext does not survive a redirect — the new
 * configuration screen therefore showed no feedback at all on a validation failure. The flash
 * bag does survive, and @BackOfficeDefaultTwig/base.html.twig renders it through app.flashes.
 *
 * ParserContext is still populated so that a Smarty back-office (Thelia 2) keeps working.
 */
trait ReportsFormErrorsTrait
{
    protected function reportFormError(BaseForm $form, string $message): void
    {
        $form->setErrorMessage($message);

        $this->getParserContext()
            ->addForm($form)
            ->setGeneralError($message);

        $this->flashError($message);
    }

    protected function flashError(string $message): void
    {
        $session = $this->getRequest()->getSession();

        if ($session instanceof FlashBagAwareSessionInterface) {
            $session->getFlashBag()->add('danger', $message);
        }
    }
}
