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
use Thelia\Core\Security\Exception\TokenAuthenticationException;
use Thelia\Tools\TokenProvider;

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

    /**
     * Maximum accepted length, generous enough for an international number with separators.
     */
    private const MAX_CELLPHONE_LENGTH = 30;

    /**
     * Digits, spaces, dots, dashes, brackets and a leading +. Deliberately permissive on
     * formatting — the carrier validates the number itself — but it rejects anything that is
     * not phone-shaped.
     */
    private const CELLPHONE_PATTERN = '/^\+?[0-9 .\-()]+$/';

    /**
     * State-changing POST, so it requires the Thelia CSRF token.
     *
     * The caller must send it as `_token`; a Flexy/Stimulus fetch gets the value from
     * `{{ assignToken() }}` in the template that renders the delivery step. See the Readme.
     */
    #[Route(path: '/save', name: 'save', methods: ['POST'])]
    public function save(Request $request, TokenProvider $tokenProvider): Response
    {
        // checkToken() never returns false: it returns true or throws
        // TokenAuthenticationException — both when the token is wrong and when none was ever
        // assigned to the session. Testing its return value alone yields a 500 instead of a 403.
        try {
            $tokenProvider->checkToken((string) $request->get('_token', ''));
        } catch (TokenAuthenticationException) {
            return new Response(status: Response::HTTP_FORBIDDEN);
        }

        $cellphone = trim((string) $request->get('predict_cellphone', ''));
        $session = $request->getSession();

        if ('' === $cellphone) {
            $session->remove(self::SESSION_KEY);

            return new Response(status: Response::HTTP_NO_CONTENT);
        }

        // Only ever prefills the session, but it is echoed back by value() and ends up in the
        // order, so keep obviously bogus input out.
        if (\strlen($cellphone) > self::MAX_CELLPHONE_LENGTH
            || 1 !== preg_match(self::CELLPHONE_PATTERN, $cellphone)) {
            return new Response(status: Response::HTTP_BAD_REQUEST);
        }

        $session->set(self::SESSION_KEY, $cellphone);

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
