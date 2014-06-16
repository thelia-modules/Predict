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
 * Class AbstractPriceForm
 * @package Predict\Form
 * @author Benjamin Perche <bperche@openstudio.fr>
 */
abstract class AbstractPriceForm extends BaseForm
{
    protected $area = null;
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
                "label_attr" => ["for"=>$this->getName()."_area"],
                "constraints" => array(
                    new Callback([
                        "methods"=> array(
                            array($this, "checkArea")
                        )
                    ])
                ),
            ))
            ->add("weight","number", array(
                "label" => Translator::getInstance()->trans("Weight up to ... (kg)",[], Predict::MESSAGE_DOMAIN),
                "label_attr" => ["for" => $this->getName()."_weight"],
                "constraints" => [
                    new GreaterThan(["value"=>0]),
                    new Callback([
                        "methods"=> array(
                            array($this, "weightExists")
                        )
                    ])
                ],
            ))
        ;
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

    public function weightExists($value, ExecutionContextInterface $context)
    {
        $translator = Translator::getInstance();
        if ($this->area === null || $this->area_id === null) {
            $context->addViolation(
                $translator->trans(
                    "The area must be defined before trying to check the weight",
                    [], Predict::MESSAGE_DOMAIN
                )
            );
        } else {
            $weight_check = @(bool) $this->getWeightCheck();
            $msg = "The weight \"%weight\" " . ( $weight_check ? "doens't":"already" ) . " exist in the area: %area";

            if (PricesQuery::sliceExists($this->area_id, $value) !== $weight_check) {
                $context->addViolation(
                    $translator->trans(
                        $msg,
                        [
                            "%weight"   => $value     ,
                            "%area"     => $this->area,
                        ],
                        Predict::MESSAGE_DOMAIN
                    )
                );
            }
        }
    }

    /**
     * @return bool
     *
     * return true if the weight has to exist or false if not
     */
    abstract protected function getWeightCheck();

}
