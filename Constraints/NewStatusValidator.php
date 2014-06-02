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

namespace Predict\Constraints;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Thelia\Core\Translation\Translator;

/**
 * Class NewStatusValidator
 * @package Predict\Constraints
 * @author Benjamin Perche <bperche@openstudio.fr>
 */
class NewStatusValidator extends ConstraintValidator
{
    /**
     * Checks if the passed value is valid.
     *
     * @param mixed      $value      The value that should be validated
     * @param Constraint $constraint The constraint for the validation
     *
     * @api
     */
    public function validate($value, Constraint $constraint)
    {
        if (null === $value || '' === $value) {
            return;
        }

        if (!in_array($value, ["nochange", "processing", "sent"] )) {
            $this->context->addViolation(
                Translator::getInstance()->trans(
                    $constraint->message
                ),
                array(
                    '{{ value }}' => $value
                )
            );
        }
    }

}
