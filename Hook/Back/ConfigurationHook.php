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
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace Predict\Hook\Back;

use Predict\Form\AddPriceForm;
use Predict\Form\ConfigureForm;
use Predict\Form\DeletePriceForm;
use Predict\Form\EditPriceForm;
use Predict\Form\ExportForm;
use Predict\Form\FreeShipping;
use Predict\Form\FreeShippingAmountForm;
use Predict\Form\TaxRuleForm;
use Predict\Model\PredictQuery;
use Predict\Model\PricesQuery;
use Predict\Predict;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormView;
use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Form\TheliaFormFactory;
use Thelia\Core\Hook\BaseHook;
use Thelia\Core\Template\Parser\ParserResolver;
use Thelia\Core\Translation\Translator;
use Thelia\Model\AreaDeliveryModuleQuery;
use Thelia\Model\AreaQuery;
use Thelia\Model\ConfigQuery;
use Thelia\Model\CurrencyQuery;
use Thelia\Model\Order;

/**
 * Renders the Predict module configuration screen in the default-twig back-office.
 *
 * Replaces the Thelia 2 AdminIncludes/module_configuration.html Smarty template: the
 * data that used to be produced by {loop} tags is prepared here in PHP and handed to Twig.
 */
class ConfigurationHook extends BaseHook
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
            'module.configuration' => [
                ['type' => 'back', 'method' => 'onModuleConfiguration'],
            ],
            'module.config-js' => [
                ['type' => 'back', 'method' => 'onModuleConfigurationJs'],
            ],
        ];
    }

    public function onModuleConfiguration(HookRenderEvent $event): void
    {
        if ('Predict' !== $event->getArgument('modulecode')) {
            return;
        }

        $defaultCurrency = CurrencyQuery::create()->filterByByDefault(1)->findOne();

        $event->add(
            $this->render('Predict/module-configuration.html.twig', [
                'access_errors' => $this->getAccessErrors(),
                'exapaq_configured' => (bool) ConfigQuery::read('store_exapaq_account'),
                'is_free_shipping' => (bool) ConfigQuery::read('predict_freeshipping'),
                'currency_symbol' => null !== $defaultCurrency ? $defaultCurrency->getSymbol() : '',
                'orders' => $this->getExportableOrders(),
                'areas' => $this->getAreasWithPrices(),
                'export_form' => $this->createFormView(ExportForm::getName()),
                'configure_form' => $this->createFormView(ConfigureForm::getName()),
                'free_shipping_form' => $this->createFormView(FreeShipping::getName()),
                'free_shipping_amount_form' => $this->createFormView(FreeShippingAmountForm::getName()),
                'add_price_form' => $this->createFormView(AddPriceForm::getName()),
                'edit_price_form' => $this->createFormView(EditPriceForm::getName()),
                'delete_price_form' => $this->createFormView(DeletePriceForm::getName()),
                'tax_rule_form' => $this->createFormView(TaxRuleForm::getName()),
            ])
        );
    }

    public function onModuleConfigurationJs(HookRenderEvent $event): void
    {
        if ('Predict' !== $event->getArgument('modulecode')) {
            return;
        }

        $event->add($this->render('Predict/module-config-js.html.twig'));
    }

    private function createFormView(string $formName): FormView
    {
        return $this->formFactory->createForm($formName, FormType::class)->createView()->getView();
    }

    /**
     * @return array<int, array{message: string, file: string}>
     */
    private function getAccessErrors(): array
    {
        $errors = [];
        $translator = Translator::getInstance();
        $dir = \dirname(__DIR__, 2).'/Config/';

        if (!is_readable($dir)) {
            $errors[] = ['message' => $translator->trans("Can't read Config directory", [], Predict::MESSAGE_DOMAIN), 'file' => ''];
        }
        if (!is_writable($dir)) {
            $errors[] = ['message' => $translator->trans("Can't write Config directory", [], Predict::MESSAGE_DOMAIN), 'file' => ''];
        }

        if (false !== ($handle = @opendir($dir))) {
            while (false !== ($file = readdir($handle))) {
                if (\strlen($file) > 5 && str_ends_with($file, '.json')) {
                    if (!is_readable($dir.$file)) {
                        $errors[] = ['message' => $translator->trans("Can't read file", [], Predict::MESSAGE_DOMAIN), 'file' => 'Predict/Config/'.$file];
                    }
                    if (!is_writable($dir.$file)) {
                        $errors[] = ['message' => $translator->trans("Can't write file", [], Predict::MESSAGE_DOMAIN), 'file' => 'Predict/Config/'.$file];
                    }
                }
            }
            closedir($handle);
        }

        return $errors;
    }

    /**
     * @return array<int, array{id: int, ref: string, created_at: ?\DateTimeInterface, total: float, currency_symbol: string}>
     */
    private function getExportableOrders(): array
    {
        $rows = [];

        /** @var Order $order */
        foreach (PredictQuery::getOrders()->find() as $order) {
            $tax = 0;
            $currency = $order->getCurrency();

            $rows[] = [
                'id' => $order->getId(),
                'ref' => $order->getRef(),
                'created_at' => $order->getCreatedAt(),
                'total' => (float) $order->getTotalAmount($tax),
                'currency_symbol' => null !== $currency ? $currency->getSymbol() : '',
            ];
        }

        return $rows;
    }

    /**
     * @return array<int, array{id: int, name: string, slices: array<int, array{max_weight: string, price: float}>}>
     */
    private function getAreasWithPrices(): array
    {
        $moduleId = Predict::getModuleId();

        $areaIds = [];
        foreach (AreaDeliveryModuleQuery::create()->filterByDeliveryModuleId($moduleId)->find() as $link) {
            $areaIds[] = $link->getAreaId();
        }

        if ([] === $areaIds) {
            return [];
        }

        $prices = PricesQuery::getPrices();
        $result = [];

        foreach (AreaQuery::create()->filterById($areaIds)->orderByName()->find() as $area) {
            $areaId = $area->getId();

            $slices = [];
            if (isset($prices[$areaId]['slices']) && \is_array($prices[$areaId]['slices'])) {
                $areaSlices = $prices[$areaId]['slices'];
                ksort($areaSlices);
                foreach ($areaSlices as $maxWeight => $price) {
                    $slices[] = ['max_weight' => (string) $maxWeight, 'price' => (float) $price];
                }
            }

            $result[] = [
                'id' => $areaId,
                'name' => $area->getName(),
                'slices' => $slices,
            ];
        }

        return $result;
    }
}
