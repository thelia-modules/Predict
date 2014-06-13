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
use Predict\Model\PricesQuery;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\ExecutionContextInterface;
use Thelia\Core\Translation\Translator;
use Thelia\Form\BaseForm;
use Thelia\Model\AreaQuery;

/**
 * Class AddPriceForm
 * @package Predict\Form
 * @author Benjamin Perche <bperche@openstudio.fr>
 */
class AddPriceForm extends BaseForm
{

    protected $area     = null;
    protected $area_id  = null;
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
        $translator = Translator::getInstance();

        $this->formBuilder
            ->add("price", "number", array(
                "label" => $translator->trans("Price (€)"),
                "label_attr" => ["for"=>"create_price_slice_form_price"],
                "constraints" => [
                    new GreaterThanOrEqual(["value"=>0]),
                ],
            ))
            ->add("area", "integer", array(
                "label_attr" => ["for"=>"create_price_slice_form_area"],
                "constraints" => array(
                    new Callback([
                        "methods"=> array(
                            array($this, "checkArea")
                        )
                    ])
                ),
            ))
            ->add("weight","number", array(
                "label" => $translator->trans("Weight up to ... (kg)"),
                "label_attr" => ["for" => "create_price_slice_form_weight"],
                "constraints" => [
                    new GreaterThan(["value"=>0]),
                    new Callback([
                        "methods"=> array(
                            array($this, "weightNotExists")
                        )
                    ])
                ],
            ))
        ;
    }

    /**
     * @return string the name of you form. This name must be unique
     */
    public function getName()
    {
        return "add_price_form";
    }

    public function checkArea($value, ExecutionContextInterface $context)
    {
        $check = AreaQuery::create()
            ->findPk($value);

        if ($check === null) {
            $context->addViolation(
                Translator::getInstance()->trans("The area \"%id\" doesn't exist", ["%id"=>$value])
            );
        } else {
            $this->area = $check->getName();
            $this->area_id = $value;
        }
    }

    public function weightNotExists($value, ExecutionContextInterface $context)
    {
        if ($this->area === null || $this->area_id === null) {
            $context->addViolation(
                Translator::getInstance()->trans("The area must be defined before trying to check the weight")
            );
        }

        if (PricesQuery::sliceExists($this->area_id, $value)) {
            $context->addViolation(
                Translator::getInstance()->trans(
                    "The weight \"%weight\" already exist in the area: %area",
                    [
                        "%weight"   => $value     ,
                        "%area"     => $this->area,
                    ]
                )
            );
        }
    }

}
