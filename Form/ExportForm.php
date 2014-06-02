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
use Predict\Constraints\NewStatus;
use Predict\Predict;
use Thelia\Core\Translation\Translator;
use Thelia\Form\BaseForm;
use Thelia\Model\Order;
use Thelia\Model\OrderQuery;
use Thelia\Model\OrderStatus;

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
        $orders = OrderQuery::create()
            ->filterByOrderStatus(array(OrderStatus::CODE_PAID, OrderStatus::CODE_PROCESSING))
            ->filterByDeliveryModuleId(Predict::getModuleId())
            ->find()
        ;

        $this->formBuilder
            ->add("new_status", "text", array(
                "label"         => Translator::getInstance()->trans("Change exported orders status"),
                "label_attr"    => array( "for" => "new_status" )                                   ,
                "required"      => true                                                             ,
                "constraints"   => array( new NewStatus() )                                         ,
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

}
