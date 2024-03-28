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
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Thelia\Core\Translation\Translator;

/**
 * Class AddPriceForm
 * @package Predict\Form
 * @author Benjamin Perche <bperche@openstudio.fr>
 */
class AddPriceForm extends AbstractPriceForm
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
        parent::buildForm();

        $this->formBuilder
            ->add("price", NumberType::class, array(
                "label" => Translator::getInstance()->trans("Price (€)"),
                "label_attr" => ["for"=>$this->getName()."_price"],
                "constraints" => [
                    new GreaterThanOrEqual(["value"=>0]),
                ],
            ))
        ;
    }

    /**
     * @return string the name of you form. This name must be unique
     */
    public static function getName()
    {
        return "create_price_slice_form";
    }

    /**
     * @return bool
     *
     * return true if the weight has to exist or false if not
     */
    protected function getWeightCheck()
    {
        return false;
    }

}
