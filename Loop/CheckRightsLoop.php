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

namespace Predict\Loop;
use Predict\Predict;
use Thelia\Core\Template\Element\ArraySearchLoopInterface;
use Thelia\Core\Template\Element\BaseLoop;
use Thelia\Core\Template\Element\LoopResult;
use Thelia\Core\Template\Element\LoopResultRow;
use Thelia\Core\Template\Loop\Argument\ArgumentCollection;
use Thelia\Core\Translation\Translator;

/**
 * Class CheckRightsLoop
 * @package Predict\Loop
 * @author Benjamin Perche <bperche@openstudio.fr>
 */
class CheckRightsLoop extends BaseLoop implements ArraySearchLoopInterface
{
    protected function getArgDefinitions()
    {
        return new ArgumentCollection();
    }

    public function buildArray()
    {
        $ret = array();

        $translator = Translator::getInstance();

        $dir = __DIR__."/../Config/";
        if (!is_readable($dir)) {
            $ret[] = array(
                "ERRMES"=>$translator->trans(
                    "Can't read Config directory",[],Predict::MESSAGE_DOMAIN
                ),
                "ERRFILE"=>""
            );
        }
        if (!is_writable($dir)) {
            $ret[] = array(
                "ERRMES"=>$translator->trans(
                        "Can't write Config directory",[],Predict::MESSAGE_DOMAIN
                    ),
                "ERRFILE"=>""
            );
        }
        if ($handle = opendir($dir)) {
            while (false !== ($file = readdir($handle))) {
                if (strlen($file) > 5 && substr($file, -5) === ".json") {
                    if (!is_readable($dir.$file)) {
                        $ret[] = array(
                            "ERRMES"=>$translator->trans(
                                    "Can't read file",[],Predict::MESSAGE_DOMAIN
                                ),
                            "ERRFILE"=>"Predict/Config/".$file);
                    }
                    if (!is_writable($dir.$file)) {
                        $ret[] = array(
                            "ERRMES"=>$translator->trans(
                                    "Can't write file",[],Predict::MESSAGE_DOMAIN
                                ),
                            "ERRFILE"=>"Predict/Config/".$file
                        );
                    }
                }
            }
        }

        return $ret;
    }
    public function parseResults(LoopResult $loopResult)
    {
        foreach ($loopResult->getResultDataCollection() as $arr) {
            $loopResultRow = new LoopResultRow();
            $loopResultRow->set("ERRMES", $arr["ERRMES"])
                ->set("ERRFILE", $arr["ERRFILE"]);
            $loopResult->addRow($loopResultRow);
        }

        return $loopResult;
    }
}
