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
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
        $account = ConfigQuery::read("store_exapaq_account");

        if (!is_numeric($account)) {
            ConfigQuery::write("store_exapaq_account", 0);
            $account = 0;
        }

        $translator = Translator::getInstance();

        $this->formBuilder
            ->add("account_number", IntegerType::class, array(
                "label"         => $translator->trans("Account number",[], Predict::MESSAGE_DOMAIN)     ,
                "label_attr"    => ["for" => "account_number"]                                          ,
                "constraints"   => [new NotBlank()]                                                     ,
                "required"      => true                                                                 ,
                "data"          => $account                                                             ,
            ))
            ->add("store_cellphone", TextType::class, array(
                "label"         => $translator->trans("Store's cellphone",[], Predict::MESSAGE_DOMAIN)  ,
                "label_attr"    => ["for" => "store_cellphone"]                                         ,
                "required"      => false                                                                ,
                "data"          => ConfigQuery::read("store_cellphone")                                 ,
            ))
            ->add("predict_option", CheckboxType::class, array(
                "label"         => $translator->trans("Predict SMS option", [], Predict::MESSAGE_DOMAIN),
                "label_attr"    => ["for" => "predict_option"]                                          ,
                "required"      => false                                                                ,
                "data"          => @(bool) ConfigQuery::read("store_predict_option")                    ,
            ))
        ;
    }

    /**
     * @return string the name of you form. This name must be unique
     */
    public static function getName()
    {
        return "predict_configure";
    }

}
