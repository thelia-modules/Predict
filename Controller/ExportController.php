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
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\Event\Order\OrderEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Model\OrderQuery;
use Thelia\Model\OrderStatus;
use Thelia\Model\OrderStatusQuery;
use Thelia\Tools\URL;

/**
 * Class ExportController
 * @package Predict\Controller
 * @author Benjamin Perche <bperche@openstudio.fr>
 */
class ExportController extends BaseAdminController
{
    #[Route('/admin/module/predict/export', name: 'predict.export', methods: ['POST'])]
    public function export(EventDispatcherInterface $eventDispatcher)
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

            $form           = $this->createForm(ExportForm::getName());
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

                    $eventDispatcher->dispatch($event, TheliaEvents::ORDER_UPDATE_STATUS);
                }
            }

        } catch (\Exception $e) {
            return $this->generateRedirect(
                URL::getInstance()->absoluteUrl('/admin/module/Predict', ['tab' => 'export'])
            );

        }

        return $this->createResponse($export_data);
    }

    #[Route('/admin/module/predict/export/{order_id}', name: 'predict.single.export', methods: ['POST'], requirements: ['order_id' => '\d+'])]
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

            $form = $this->createForm(SingleExportForm::getName());
            $vform = $this->validateForm($form, "post");


            $export->addEntry(
                new ExportEntry(
                    $order,
                    $vform->get("guaranty")->getData()
                )
            );

            $export_data = $export->doExport();
        } catch (\Exception $e) {
            $this->generateRedirectFromRoute(
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
        return new Response(
            $content,
            200,
            array(
                'Content-Type'          => 'application/csv-tab-delimited-table',
                'Content-disposition'   => 'filename=record.dat'                ,
            )
        );
    }
}
