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

use Predict\Controller\Traits\ReportsFormErrorsTrait;

use Predict\Form\AddPriceForm;
use Predict\Form\ConfigureForm;
use Predict\Form\DeletePriceForm;
use Predict\Form\EditPriceForm;
use Predict\Form\FreeShipping;
use Predict\Form\FreeShippingAmountForm;
use Predict\Model\PricesQuery;
use Predict\Predict;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Form\Exception\FormValidationException;
use Thelia\Model\ConfigQuery;
use Thelia\Tools\URL;

/**
 * Class ConfigurationController
 * @package Predict\Controller
 * @author Benjamin Perche <bperche@openstudio.fr>
 */
class ConfigurationController extends BaseAdminController
{
    use ReportsFormErrorsTrait;

    #[Route('/admin/module/predict/freeshipping', name: 'predict.freeshipping')]
    public function setFreeShipping()
    {
        if (null !== $response = $this->checkAuth([AdminResources::MODULE], ['Predict'], AccessManager::UPDATE)) {
            return $response;
        }

        $error_msg = false;
        $save_mode = null;
        $form = $this->createForm(FreeShipping::getName());
        $response=null;

        try {
            $vform = $this->validateForm($form);
            $save_mode = $this->getRequest()->get("save_mode");
            $data = $vform->get('freeshipping')->getData();

            ConfigQuery::write("predict_freeshipping", $data);
            $response = new Response('');
        } catch (FormValidationException $ex) {
            // Form cannot be validated
            $error_msg = $this->createStandardFormValidationErrorMessage($ex);
            $response = new JsonResponse(array("error"=>$error_msg), 500);
        } catch (\Exception $ex) {
            // Any other error
            $error_msg = $ex->getMessage();
            $response = new JsonResponse(array("error"=>$error_msg), 500);
        }

        if (!empty($save_mode)) {
            if (false !== $error_msg) {
                $this->reportFormError($form, $error_msg);
            }

            if ($save_mode !== "stay") {
                return $this->generateRedirectFromRoute(
                    "admin.module",
                    [],
                    ['_controller' => 'Thelia\\Controller\\Admin\\ModuleController::indexAction']
                );
            }

            return $this->generateRedirect(
                URL::getInstance()->absoluteUrl('/admin/module/Predict', ['tab' => 'prices_slices_tab'])
            );
        }

        return $response;
    }

    #[Route('/admin/module/predict/freeshipping_amount', name: 'predict.freeshipping_amount', methods: ['POST'])]
    public function setFreeShippingAmount()
    {
        if (null !== $response = $this->checkAuth([AdminResources::MODULE], ['Predict'], AccessManager::UPDATE)) {
            return $response;
        }

        $form = $this->createForm(FreeShippingAmountForm::getName());

        try {
            $vform = $this->validateForm($form);
            $data = (float) $vform->get('amount')->getData();

            Predict::setFreeShippingAmount($data);
        } catch (\Exception $e) {
            $this->setupFormErrorContext(
                "Setting free shipping amount",
                $e->getMessage(),
                $form,
                $e
            );

            // setupFormErrorContext() only fills ParserContext, which the redirect below
            // discards.
            $this->flashError($e->getMessage());
        }

        return $this->generateRedirect(
            URL::getInstance()->absoluteUrl('/admin/module/Predict', ['tab' => 'prices_slices_tab'])
        );
    }

    #[Route('/admin/module/predict/configure', name: 'predict.configure')]
    public function exapaqConfigure()
    {
        if (null !== $response = $this->checkAuth([AdminResources::MODULE], ['Predict'], AccessManager::UPDATE)) {
            return $response;
        }

        $error_msg  = false                                 ;
        $save_mode  = "stay"                                ;
        $form       = $this->createForm(ConfigureForm::getName());

        try {
            $vform = $this->validateForm($form)                                                             ;

            $save_mode = $this->getRequest()->request->get("save_mode")                                     ;

            ConfigQuery::write("store_exapaq_account", $vform->get("account_number")->getData())            ;
            ConfigQuery::write("store_cellphone", $vform->get("store_cellphone")->getData())                ;
            ConfigQuery::write("store_predict_option", $vform->get("predict_option")->getData() ? "1":"")   ;
        } catch (FormValidationException $ex) {
            // Form cannot be validated
            $error_msg = $this->createStandardFormValidationErrorMessage($ex);
        } catch (\Exception $ex) {
            // Any other error
            $error_msg = $ex->getMessage();
        }

        if (false !== $error_msg) {
            $this->reportFormError($form, $error_msg);
        }

        if ($save_mode !== "stay") {
            return $this->generateRedirectFromRoute(
                "admin.module",
                [],
                ['_controller' => 'Thelia\\Controller\\Admin\\ModuleController::indexAction']
            );
        }

        return $this->generateRedirect(
            URL::getInstance()->absoluteUrl('/admin/module/Predict', ['tab' => 'configure'])
        );
    }

    #[Route('/admin/module/predict/price/add', name: 'predict.add.price', methods: ['POST'])]
    public function addPrice()
    {
        if (null !== $response = $this->checkAuth([AdminResources::MODULE], ['Predict'], AccessManager::CREATE)) {
            return $response;
        }

        $error_msg = false;

        $form = $this->createForm(AddPriceForm::getName());

        $areaId = 0;

        try {
            $vform = $this->validateForm($form, "post");

            $areaId = $vform->get("area")->getData();

            PricesQuery::setPostageAmount(
                $vform->get("price")->getData(),
                $areaId,
                $vform->get("weight")->getData()
            );
        } catch (FormValidationException $e) {
            $error_msg = $this->createStandardFormValidationErrorMessage($e);
        } catch (\Exception $e) {
            $error_msg = $e->getMessage();
        }

        if (false !== $error_msg) {
            $this->reportFormError($form, $error_msg);
        }

        return $this->generateRedirect(
            URL::getInstance()->absoluteUrl('/admin/module/Predict', ['tab' => 'prices_slices_tab']) . "#area-$areaId"
        );
    }

    #[Route('/admin/module/predict/price/edit', name: 'predict.edit.price', methods: ['POST'])]
    public function editPrice()
    {
        if (null !== $response = $this->checkAuth([AdminResources::MODULE], ['Predict'], AccessManager::UPDATE)) {
            return $response;
        }

        $error_msg = false;

        $form = $this->createForm(EditPriceForm::getName());

        $areaId = 0;

        try {
            $vform = $this->validateForm($form, "post");

            $areaId = $vform->get("area")->getData();

            PricesQuery::setPostageAmount(
                $vform->get("price")->getData(),
                $areaId,
                $vform->get("weight")->getData()
            );
        } catch (FormValidationException $e) {
            $error_msg = $this->createStandardFormValidationErrorMessage($e);
        } catch (\Exception $e) {
            $error_msg = $e->getMessage();
        }

        if (false !== $error_msg) {
            $this->reportFormError($form, $error_msg);
        }

        return $this->generateRedirect(
            URL::getInstance()->absoluteUrl('/admin/module/Predict', ['tab' => 'prices_slices_tab']) . "#area-$areaId"
        );
    }

    #[Route('/admin/module/predict/price/delete', name: 'predict.delete.price', methods: ['POST'])]
    public function deletePrice()
    {
        if (null !== $response = $this->checkAuth([AdminResources::MODULE], ['Predict'], AccessManager::DELETE)) {
            return $response;
        }

        $error_msg = false;

        $form = $this->createForm(DeletePriceForm::getName());

        $areaId = 0;

        try {
            $vform = $this->validateForm($form, "post");

            $areaId = $vform->get("area")->getData();

            PricesQuery::setPostageAmount(
                false,
                $areaId,
                $vform->get("weight")->getData()
            );
        } catch (FormValidationException $e) {
            $error_msg = $this->createStandardFormValidationErrorMessage($e);
        } catch (\Exception $e) {
            $error_msg = $e->getMessage();
        }

        if (false !== $error_msg) {
            $this->reportFormError($form, $error_msg);
        }

        return $this->generateRedirect(
            URL::getInstance()->absoluteUrl('/admin/module/Predict', ['tab' => 'prices_slices_tab']) . "#area-$areaId"
        );
    }
}
