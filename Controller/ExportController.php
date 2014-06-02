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

namespace Predict\Controller;
use Predict\Export\ExportEntry;
use Predict\Export\PredictExport;
use Predict\Form\ExportForm;
use Predict\Predict;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\Event\Order\OrderEvent;
use Thelia\Core\HttpFoundation\Response;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Model\OrderQuery;
use Thelia\Model\OrderStatus;

/**
 * Class ExportController
 * @package Predict\Controller
 * @author Benjamin Perche <bperche@openstudio.fr>
 */
class ExportController extends BaseAdminController
{
    public function export()
    {

        if (null !== $response = $this->checkAuth(array(AdminResources::MODULE), array('Predict'), AccessManager::UPDATE)) {
            return $response;
        }

        $orders = OrderQuery::create()
            ->filterByOrderStatus(array(OrderStatus::CODE_PAID, OrderStatus::CODE_PROCESSING))
            ->filterByDeliveryModuleId(Predict::getModuleId())
            ->find()
        ;

        $export         = new PredictExport()   ;
        $export_data    = ""                    ;

        /**
         * Validate the form and checks which order(s) must be exported
         */
        try {

            $form           = new ExportForm($this->getRequest())   ;
            $vform          = $this->validateForm($form)            ;
            $entries        = array()                               ;

            /** @var \Thelia\Model\Order $order */
            foreach ($orders as $order) {
                if ($vform->get("order_".$order->getId())->getData()) {
                    $entries[] = $entry = new ExportEntry(
                        $order,
                        $vform->get("guaranty_".$order->getId())->getData()
                    );

                    $export->addEntry($entry);
                }
            }

            /**
             * Be sure that the export is done before updating the order status
             */

            $export_data = $export->doExport();

            $status = null;
            switch ($vform->get("new_status")->getData()) {
                case "processing":
                    $status = OrderStatus::CODE_PROCESSING;
                    break;
                case "sent":
                    $status = OrderStatus::CODE_SENT;
                    break;
            }

            if ($status !== null) {
                /** @var ExportEntry $entry */
                foreach ($entries as $entry) {
                    $event = new OrderEvent($entry->getOrder());

                    $event->setStatus($status);

                    $this->dispatch($event);
                }
            }

        } catch (\Exception $e) {

            $this->redirectToRoute(
                "admin.module.configure",
                array(
                    "errmes" => $e->getMessage(),
                ),
                array (
                    "current_tab"   => "export_tab"                                                     ,
                    "module_code"   => "Predict"                                                        ,
                    "_controller"   => "Thelia\\Controller\\Admin\\ModuleController::configureAction"   ,
                )
            );

        }

        return Response::create(
            $export_data,
            200,
            array(
                'Content-Type'          => 'application/csv-tab-delimited-table',
                'Content-disposition'   => 'filename=record.dat'                ,
            )
        );

    }

}
