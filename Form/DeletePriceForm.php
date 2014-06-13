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
use Predict\Predict;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\ExecutionContextInterface;
use Thelia\Core\Translation\Translator;
use Thelia\Form\BaseForm;
use Thelia\Model\AreaQuery;

/**
 * Class DeletePriceForm
 * @package Predict\Form
 * @author Benjamin Perche <bperche@openstudio.fr>
 */
class DeletePriceForm extends BaseForm
{
    protected $area_id = null;

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
            ->add("area", "integer", array(
                "constraints" => array(
                    new Callback([
                        "methods"=> array(
                            array($this, "checkArea")
                        )
                    ])
                ),
            ))
            ->add("weight", "number", array(
                "constraints" => array(
                    new GreaterThan(["value"=>0]),
                    new Callback([
                        "methods" => array(
                            array($this, "weightExists")
                        )
                    ])
                )
            ));
    }

    /**
     * @return string the name of you form. This name must be unique
     */
    public function getName()
    {
        return "delete_price_form";
    }

    public function checkArea($value, ExecutionContextInterface $context)
    {
        $check = AreaQuery::create()
            ->findPk($value);

        if($check === null) {
            $context->addViolation(
                Translator::getInstance()->trans("The area \"%id\" doesn't exist", ["%id"=>$value])
            );
        } else {
            $this->area_id = $value;
        }
    }

    public function weightExists($value, ExecutionContextInterface $context)
    {
        if($this->area_id === null) {
            $context->addViolation(
                Translator::getInstance()->trans("The area must be defined before trying to check the weight")
            );
        }

        try {
            PricesQuery::getPostageAmount($this->area_id, $value);
        } catch(\Exception $e) {
            $context->addViolation(
                Translator::getInstance()->trans(
                    "The weight \"%weight\" doesn't exist in the area: %area",
                    [
                        "%weight"   =>$value        ,
                        "%area"     =>$this->area_id,
                    ]
                )
            );
        }
    }

} 