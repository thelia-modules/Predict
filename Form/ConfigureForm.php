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
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
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

        $this->formBuilder
            ->add("account_number", "integer", array(
                "label"         => Translator::getInstance()->trans("Account number")       ,
                "label_attr"    => ["for" => "account_number"]                              ,
                "constraints"   => [new NotBlank()]                                         ,
                "required"      => true                                                     ,
                "data"          => ConfigQuery::read("store_exapaq_account")                ,
            ))
            ->add("store_cellphone", "text", array(
                "label"         => Translator::getInstance()->trans("Store's cellphone")    ,
                "label_attr"    => ["for" => "store_cellphone"]                             ,
                "required"      => false                                                    ,
                "data"          => ConfigQuery::read("store_cellphone")                     ,
            ))
            ->add("predict_option", "checkbox", array(
                "label"         => Translator::getInstance()->trans("Predict SMS option")   ,
                "label_attr"    => ["for" => "predict_option"]                              ,
                "required"      => false                                                    ,
                "data"          => @(bool)ConfigQuery::read("store_predict_option")         ,
            ))
        ;

        /*
         *  ConfigQuery::write("store_exapaq_account", $vform->get("account_number")->getData());
            ConfigQuery::write("store_cellphone", $vform->get("store_cellphone")->getData())    ;
            ConfigQuery::write("store_predict_option", $vform->get("predict_option")->getData());
         *
         */
    }

    /**
     * @return string the name of you form. This name must be unique
     */
    public function getName()
    {
        return "configure_exapaq_account_form";
    }

} 