<?php

namespace Predict\Controller;

use Predict\Predict;
use Predict\Form\TaxRuleForm;
use Symfony\Component\Routing\Annotation\Route;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Core\Translation\Translator;
use Thelia\Form\Exception\FormValidationException;
use Thelia\Tools\URL;

#[Route('/admin/module/Predict/tax_rule', name: 'predict_tax_rule_')]
class TaxRuleController extends BaseAdminController
{
    #[Route('/save', name: 'save')]
    public function saveTaxRule()
    {
        if (null !== $response = $this->checkAuth(AdminResources::MODULE, Predict::MESSAGE_DOMAIN, AccessManager::UPDATE)) {
            return $response;
        }

        $taxRuleForm = $this->createForm(TaxRuleForm::getName());

        $message = false;

        $url = '/admin/module/Predict';

        try {
            $form = $this->validateForm($taxRuleForm);

            // Get the form field values
            $data = $form->getData();

            Predict::setConfigValue(Predict::PREDICT_TAX_RULE_ID, $data["tax_rule_id"]);

        } catch (FormValidationException $ex) {
            $message = $this->createStandardFormValidationErrorMessage($ex);
        } catch (\Exception $ex) {
            $message = $ex->getMessage();
        }

        if ($message !== false) {
            $this->setupFormErrorContext(
                Translator::getInstance()->trans('Error', [], Predict::MESSAGE_DOMAIN),
                $message,
                $taxRuleForm,
                $ex
            );
        }

        return $this->generateRedirect(URL::getInstance()->absoluteUrl($url, [ 'tab' => 'tax_rule']));
    }
}
