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

namespace Predict\Form;

use Predict\Predict;
use Symfony\Component\Validator\Constraints\NotBlank;
use Thelia\Core\Translation\Translator;
use Thelia\Form\BaseForm;
use Thelia\Model\ConfigQuery;

/**
 * Class ConfigureForm
 * @package Predict\Form
 * @author Benjamin Perche <bperche@openstudio.fr>
 */
class ConfigureForm extends BaseForm
{
    /**
     *
     * in this function you add all the fields you need for your Form.
     * Form this you have to call add method on $this->formBuilder attribute :
     *
     * $this->formBuilder->add("name", "text")
     *   ->add("email", "email", array(
     *           "attr" => array(
     *               "class" => "field"
     *           ),
     *           "label" => "email",
     *           "constraints" => array(
     *               new \Symfony\Component\Validator\Constraints\NotBlank()
     *           )
     *       )
     *   )
     *   ->add('age', 'integer');
     *
     * @return null
     */
    protected function buildForm()
    {
        $dpd_code = ConfigQuery::read("dpd_account_code");

        $translator = Translator::getInstance();

        $this->formBuilder
            ->add("account_number", "integer", array(
                "label"         => $translator->trans("DPD account number",[], Predict::MESSAGE_DOMAIN),
                "label_attr"    => ["for" => "account_number"]                                          ,
                "constraints"   => [new NotBlank()]                                                     ,
                "required"      => true                                                                 ,
                "data"          => $dpd_code
            ))
            ->add("store_cellphone", "text", array(
                "label"         => $translator->trans("Store's cellphone",[], Predict::MESSAGE_DOMAIN)  ,
                "label_attr"    => ["for" => "store_cellphone"]                                         ,
                "required"      => false                                                                ,
                "data"          => ConfigQuery::read("store_cellphone")                                 ,
            ))
            ->add("predict_option", "checkbox", array(
                "label"         => $translator->trans("Predict SMS option", [], Predict::MESSAGE_DOMAIN),
                "label_attr"    => ["for" => "predict_option"]                                          ,
                "required"      => false                                                                ,
                "data"          => @(bool) Predict::getConfigValue("dpd_predict_option", false)
            ))
            ->add(
                'return_type',
                'integer',
                array(
                    'label' => $translator->trans('Choose a return service', [], Predict::MESSAGE_DOMAIN),
                    'data' => Predict::getConfigValue(Predict::KEY_RETURN_TYPE, Predict::RETURN_NONE),
                    'constraints' => array(new NotBlank()),
                    'label_attr' => array(
                        'for' => 'return_type'
                    )
                )
            )
        ;
    }

    /**
     * @return string the name of you form. This name must be unique
     */
    public function getName()
    {
        return "configure_exapaq_account_form";
    }
}
