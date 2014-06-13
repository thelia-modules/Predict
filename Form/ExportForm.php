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
use Predict\Model\PredictQuery;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\ExecutionContextInterface;
use Thelia\Core\Translation\Translator;
use Thelia\Form\BaseForm;
use Thelia\Model\Order;

/**
 * Class ExportForm
 * @package Predict\Form
 * @author Benjamin Perche <bperche@openstudio.fr>
 */
class ExportForm extends BaseForm
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
        $orders = PredictQuery::getOrders();

        $this->formBuilder
            ->add("new_status", "text", array(
                "label"         => Translator::getInstance()->trans("Change exported orders status"),
                "label_attr"    => array( "for" => "new_status" )                                   ,
                "required"      => true                                                             ,
                "constraints"   => array( new Callback([
                    "methods" => array(
                        array($this, "checkStatus")
                    )
                ]))                                                                                ,
            ));

        /** @var Order $order */
        foreach ($orders as $order) {
            $this->formBuilder
                ->add("order_".$order->getId(), "checkbox", array(
                    'label'     => $order->getRef() ,
                    'required'  => false            ,
                ))
                ->add("guaranty_".$order->getId(), "checkbox", array(
                    'required'  => false            ,
                ))
            ;
        }
    }

    /**
     * @return string the name of you form. This name must be unique
     */
    public function getName()
    {
        return "predict_export_form";
    }

    public function checkStatus($value, ExecutionContextInterface $context)
    {
        if (!in_array($value, ["nochange", "processing", "sent"])) {
            $context->addViolation(
                Translator::getInstance()->trans(
                    "The value \"%value\" is not correct, please choose: nochange, processing or sent",
                    ["%value"=>$value])
            );
        }
    }

}
