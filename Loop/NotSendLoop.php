<?php
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

namespace Predict\Loop;

use Predict\Model\PredictQuery;
use Thelia\Core\Template\Element\LoopResult;
use Thelia\Core\Template\Element\LoopResultRow;
use Thelia\Core\Template\Loop\Argument\ArgumentCollection;
use Thelia\Core\Template\Loop\Order;

/**
 * Class NotSendLoop
 * @package Predict\Loop
 * @author Benjamin Perche <bperche@openstudio.fr>
 */
class NotSendLoop extends Order
{
    /**
     *
     * define all args used in your loop
     *
     *
     * example :
     *
     * public function getArgDefinitions()
     * {
     *  return new ArgumentCollection(
     *       Argument::createIntListTypeArgument('id'),
     *           new Argument(
     *           'ref',
     *           new TypeCollection(
     *               new Type\AlphaNumStringListType()
     *           )
     *       ),
     *       Argument::createIntListTypeArgument('category'),
     *       Argument::createBooleanTypeArgument('new'),
     *       Argument::createBooleanTypeArgument('promo'),
     *       Argument::createFloatTypeArgument('min_price'),
     *       Argument::createFloatTypeArgument('max_price'),
     *       Argument::createIntTypeArgument('min_stock'),
     *       Argument::createFloatTypeArgument('min_weight'),
     *       Argument::createFloatTypeArgument('max_weight'),
     *       Argument::createBooleanTypeArgument('current'),
     *
     *   );
     * }
     *
     * @return \Thelia\Core\Template\Loop\Argument\ArgumentCollection
     */
    public function getArgDefinitions()
    {
        return new ArgumentCollection();
    }

    /**
     * this method returns a Propel ModelCriteria
     *
     * @return \Propel\Runtime\ActiveQuery\ModelCriteria
     */
    public function buildModelCriteria()
    {
        return PredictQuery::getOrders();
    }

    public function parseResults(LoopResult $loopResult)
    {
        /**  @var \Thelia\Model\Order $order */
        foreach ($loopResult->getResultDataCollection() as $order) {
            $tax = 0;
            $amount = $order->getTotalAmount($tax);
            $hasVirtualDownload = $order->hasVirtualProduct();

            $loopResultRow = new LoopResultRow($order);
            $loopResultRow
                ->set('ID', $order->getId())
                ->set('REF', $order->getRef())
                ->set('CUSTOMER', $order->getCustomerId())
                ->set('DELIVERY_ADDRESS', $order->getDeliveryOrderAddressId())
                ->set('INVOICE_ADDRESS', $order->getInvoiceOrderAddressId())
                ->set('INVOICE_DATE', $order->getInvoiceDate())
                ->set('CURRENCY', $order->getCurrencyId())
                ->set('CURRENCY_RATE', $order->getCurrencyRate())
                ->set('TRANSACTION_REF', $order->getTransactionRef())
                ->set('DELIVERY_REF', $order->getDeliveryRef())
                ->set('INVOICE_REF', $order->getInvoiceRef())
                ->set('VIRTUAL', $hasVirtualDownload)
                ->set('POSTAGE', $order->getPostage())
                ->set('POSTAGE_TAX', $order->getPostageTax())
                ->set('POSTAGE_UNTAXED', $order->getUntaxedPostage())
                ->set('POSTAGE_TAX_RULE_TITLE', $order->getPostageTaxRuleTitle())
                ->set('PAYMENT_MODULE', $order->getPaymentModuleId())
                ->set('DELIVERY_MODULE', $order->getDeliveryModuleId())
                ->set('STATUS', $order->getStatusId())
                ->set('STATUS_CODE', $order->getOrderStatus()->getCode())
                ->set('LANG', $order->getLangId())
                ->set('DISCOUNT', $order->getDiscount())
                ->set('TOTAL_TAX', $tax)
                ->set('TOTAL_AMOUNT', $amount - $tax)
                ->set('TOTAL_TAXED_AMOUNT', $amount)
                ->set('WEIGHT', $order->getWeight())
                ->set('HAS_PAID_STATUS', $order->isPaid())
                ->set('IS_PAID', $order->isPaid(false))
                ->set('IS_CANCELED', $order->isCancelled())
                ->set('IS_NOT_PAID', $order->isNotPaid())
                ->set('IS_SENT', $order->isSent())
                ->set('IS_PROCESSING', $order->isProcessing());

            $this->addOutputFields($loopResultRow, $order);

            $loopResult->addRow($loopResultRow);
        }

        return $loopResult;
    }
}
