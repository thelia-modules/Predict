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
use Predict\Form\AddPriceForm;
use Predict\Form\ConfigureForm;
use Predict\Form\DeletePriceForm;
use Predict\Form\EditPriceForm;
use Predict\Model\PricesQuery;
use Predict\Form\FreeShipping;
use Symfony\Component\HttpFoundation\JsonResponse;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\HttpFoundation\Response;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Form\Exception\FormValidationException;
use Thelia\Model\ConfigQuery;

/**
 * Class ConfigurationController
 * @package Predict\Controller
 * @author Benjamin Perche <bperche@openstudio.fr>
 */
class ConfigurationController extends BaseAdminController
{
    public function setFreeShipping()
    {
        if(null !== $response = $this->checkAuth(
            [AdminResources::MODULE],
            ['Predict'],
            AccessManager::UPDATE
        )) {
            return $response;
        }

        $form = new FreeShipping($this->getRequest());
        $response=null;

        try {
            $vform = $this->validateForm($form);
            $data = $vform->get('freeshipping')->getData();

            ConfigQuery::write("predict_freeshipping", $data);
            $response = Response::create('');
        } catch (\Exception $e) {
            $response = JsonResponse::create(array("error"=>$e->getMessage()), 500);
        }

        return $response;
    }

    public function exapaqConfigure()
    {
        if(null !== $response = $this->checkAuth(
            [AdminResources::MODULE],
            ['Predict'],
            AccessManager::UPDATE
        )) {
            return $response;
        }
        $error_msg  = false                                 ;
        $save_mode  = "stay"                                ;
        $form       = new ConfigureForm($this->getRequest());

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
            $form->setErrorMessage($error_msg);

            $this->getParserContext()
                ->addForm($form)
                ->setGeneralError($error_msg)
            ;
        }

        if ($save_mode !== "stay") {
            return $this->generateRedirectFromRoute(
                "admin.module",
                [],
                ['_controller' => 'Thelia\\Controller\\Admin\\ModuleController::indexAction']
            );
        }

        return $this->render(
            "module-configure",
            [
                "module_code"   => "Predict"    ,
                "tab"           => "configure"  ,
            ]
        );
    }

    public function addPrice()
    {
        if(null !== $response = $this->checkAuth(
            [AdminResources::MODULE],
            ['Predict'],
            AccessManager::CREATE
        )) {
            return $response;
        }

        $error_msg = false;

        $form = new AddPriceForm($this->getRequest());

        try {
            $vform = $this->validateForm($form, "post");

            PricesQuery::setPostageAmount(
                $vform->get("price")->getData(),
                $vform->get("area")->getData(),
                $vform->get("weight")->getData()
            );
        } catch (FormValidationException $e) {
            $error_msg = $this->createStandardFormValidationErrorMessage($e);
        } catch (\Exception $e) {
            $error_msg = $e->getMessage();
        }

        if (false !== $error_msg) {
            $form->setErrorMessage($error_msg);

            $this->getParserContext()
                ->addForm($form)
                ->setGeneralError($error_msg)
            ;
        }

        return  $this->render(
            "module-configure",
            [
                "module_code"   => "Predict" ,
                "tab"           => "prices"  ,
            ]
        );
    }

    public function editPrice()
    {
        if(null !== $response = $this->checkAuth(
                [AdminResources::MODULE],
                ['Predict'],
                AccessManager::UPDATE
            )) {
            return $response;
        }

        $error_msg = false;

        $form = new EditPriceForm($this->getRequest());

        try {
            $vform = $this->validateForm($form, "post");

            PricesQuery::setPostageAmount(
                $vform->get("price")->getData(),
                $vform->get("area")->getData(),
                $vform->get("weight")->getData()
            );
        } catch (FormValidationException $e) {
            $error_msg = $this->createStandardFormValidationErrorMessage($e);
        } catch (\Exception $e) {
            $error_msg = $e->getMessage();
        }

        if (false !== $error_msg) {
            $form->setErrorMessage($error_msg);

            $this->getParserContext()
                ->addForm($form)
                ->setGeneralError($error_msg)
            ;
        }

        return  $this->render(
            "module-configure",
            [
                "module_code"   => "Predict"    ,
                "tab"           => "prices"  ,
            ]
        );
    }

    public function deletePrice()
    {
        if(null !== $response = $this->checkAuth(
                [AdminResources::MODULE],
                ['Predict'],
                AccessManager::DELETE
            )) {
            return $response;
        }

        $error_msg = false;

        $form = new DeletePriceForm($this->getRequest());

        try {
            $vform = $this->validateForm($form, "post");

            PricesQuery::setPostageAmount(
                false,
                $vform->get("area")->getData(),
                $vform->get("weight")->getData()
            );
        } catch (FormValidationException $e) {
            $error_msg = $this->createStandardFormValidationErrorMessage($e);
        } catch (\Exception $e) {
            $error_msg = $e->getMessage();
        }

        if (false !== $error_msg) {
            $form->setErrorMessage($error_msg);

            $this->getParserContext()
                ->addForm($form)
                ->setGeneralError($error_msg)
            ;
        }

        return  $this->render(
            "module-configure",
            [
                "module_code"   => "Predict" ,
                "tab"           => "prices"  ,
            ]
        );
    }

}
