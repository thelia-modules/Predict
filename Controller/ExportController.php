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
use Predict\Form\SingleExportForm;
use Predict\Model\PredictQuery;
use Predict\Predict;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\Event\Order\OrderEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\HttpFoundation\Response;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Model\OrderQuery;
use Thelia\Model\OrderStatus;
use Thelia\Model\OrderStatusQuery;

/**
 * Class ExportController
 * @package Predict\Controller
 * @author Benjamin Perche <bperche@openstudio.fr>
 */
class ExportController extends BaseAdminController
{
    public function export()
    {

        if (null !== $response = $this->checkAuth(
                [AdminResources::MODULE, AdminResources::ORDER],
                ['Predict'],
                AccessManager::VIEW)
        ) {
            return $response;
        }

        $orders         = PredictQuery::getOrders() ;
        $export         = new PredictExport()       ;
        $export_data    = ""                        ;

        /**
         * Validate the form and checks which order(s) must be exported
         */
        try {

            $form           = new ExportForm($this->getRequest())   ;
            $vform          = $this->validateForm($form, "post")    ;
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
                /**
                 *  If the current user doesn't have the right to edit orders, return an error
                 */
                if (null !== $response = $this->checkAuth(
                        [AdminResources::ORDER],
                        [],
                        AccessManager::UPDATE)
                ) {
                    return $response;
                }

                /**
                 * Get status ID
                 */
                $status_id = OrderStatusQuery::create()
                    ->findOneByCode($status)
                    ->getId();

                /** @var ExportEntry $entry */
                foreach ($entries as $entry) {
                    $event = new OrderEvent($entry->getOrder());

                    $event->setStatus($status_id);

                    $this->dispatch(TheliaEvents::ORDER_UPDATE_STATUS, $event);
                }
            }

        } catch (\Exception $e) {
            return  $this->render(
                "module-configure",
                [
                    "module_code"   => "Predict"    ,
                    "tab"           => "export"  ,
                ]
            );

        }

        return $this->createResponse($export_data);
    }

    public function singleExport($order_id)
    {
        if (null !== $response = $this->checkAuth(
                [AdminResources::MODULE, AdminResources::ORDER],
                ['Predict'],
                AccessManager::VIEW)
        ) {
            return $response;
        }

        $export         = new PredictExport()   ;
        $export_data    = ""                    ;

        $order = OrderQuery::create()
            ->findPk($order_id);

        if ($order === null) {
            throw new \InvalidArgumentException("order_id ".$order_id." doesn't exist");
        }

        try {

            $form = new SingleExportForm($this->getRequest())   ;
            $vform = $this->validateForm($form, "post")         ;


            $export->addEntry(
                new ExportEntry(
                    $order,
                    $vform->get("guaranty")->getData()
                )
            );

            $export_data = $export->doExport();
        } catch (\Exception $e) {
            $this->redirectToRoute(
                'admin.order.update.view',
                array(
                    "errmes" => $e->getMessage(),
                ),
                array(
                    "_controller"   => 'Thelia\\Controller\\Admin\\OrderController::viewAction' ,
                    "order_id"      => $order_id                                                ,
                    "tab"           => "modules"                                                ,
                )
            );
        }

        return $this->createResponse($export_data);
    }

    /**
     * @param $content
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createResponse($content)
    {
        return Response::create(
            $content,
            200,
            array(
                'Content-Type'          => 'application/csv-tab-delimited-table',
                'Content-disposition'   => 'filename=record.dat'                ,
            )
        );
    }
}
