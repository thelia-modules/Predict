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

namespace Predict\Controller\Front;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Thelia\Controller\Front\BaseFrontController;

/**
 * Stores and reads the Predict cellphone number chosen at checkout, keyed on the session.
 * This is the Thelia 3 replacement for the Thelia 2 order-delivery.extra hook input: the
 * Flexy delivery step posts the value here (Stimulus) instead of relying on a page form.
 *
 * The Flexy theme must NOT reference a module-provided Twig function, otherwise the delivery
 * template fails to compile when Predict is disabled. The prefill value is therefore served
 * by this controller (called at runtime only when the Predict option is displayed).
 */
#[Route(path: '/predict/cellphone', name: 'predict.cellphone.')]
class CellphoneController extends BaseFrontController
{
    public const SESSION_KEY = 'predict_cellphone';

    #[Route(path: '/save', name: 'save', methods: ['POST'])]
    public function save(Request $request): Response
    {
        $cellphone = trim((string) $request->get('predict_cellphone', ''));
        $session = $request->getSession();

        if ('' === $cellphone) {
            $session->remove(self::SESSION_KEY);
        } else {
            $session->set(self::SESSION_KEY, $cellphone);
        }

        return new Response(status: Response::HTTP_NO_CONTENT);
    }

    #[Route(path: '/value', name: 'value', methods: ['GET'])]
    public function value(Request $request): JsonResponse
    {
        $sessionValue = $request->getSession()->get(self::SESSION_KEY);
        if (\is_string($sessionValue) && '' !== $sessionValue) {
            return new JsonResponse(['cellphone' => $sessionValue]);
        }

        $address = $this->getSecurityContext()->getCustomerUser()?->getDefaultAddress();

        return new JsonResponse(['cellphone' => $address?->getCellphone() ?? '']);
    }
}
