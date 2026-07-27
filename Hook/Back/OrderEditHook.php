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

namespace Predict\Hook\Back;

use Predict\Form\SingleExportForm;
use Predict\Predict;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Form\TheliaFormFactory;
use Thelia\Core\Hook\BaseHook;
use Thelia\Core\Template\Parser\ParserResolver;

/**
 * Renders the "Export this order for EXAPRINT" panel on the order edit screen,
 * below the Predict delivery module. Ported from AdminIncludes/order-edit.html.
 */
class OrderEditHook extends BaseHook
{
    public function __construct(
        protected TheliaFormFactory $formFactory,
        ?EventDispatcherInterface $dispatcher = null,
        ?ParserResolver $parserResolver = null,
    ) {
        parent::__construct($dispatcher, $parserResolver);
    }

    public static function getSubscribedHooks(): array
    {
        return [
            'order-edit.delivery-module-bottom' => [
                ['type' => 'back', 'method' => 'onDeliveryModuleBottom'],
            ],
        ];
    }

    public function onDeliveryModuleBottom(HookRenderEvent $event): void
    {
        if ((int) $event->getArgument('module_id') !== (int) Predict::getModuleId()) {
            return;
        }

        $event->add(
            $this->render('Predict/order-edit.html.twig', [
                'order_id' => (int) $event->getArgument('order_id'),
                'single_export_form' => $this->formFactory
                    ->createForm(SingleExportForm::getName(), FormType::class)
                    ->createView()
                    ->getView(),
            ])
        );
    }
}
