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

namespace Predict\Hook;

use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Hook\BaseHook;


/**
 * Class PredictHook
 * @package Predict\Hook
 * @author Manuel Raynaud <manu@thelia.net>
 */
class PredictHook extends BaseHook
{
    public function renderPredictCss(HookRenderEvent $event)
    {
        $content = $this->addCSS('assets/css/styles.css');
        $event->add($content);
    }

    public function renderPredict(HookRenderEvent $event)
    {
        $event->add($this->render('predict.html', ['predict_id' => $event->getArgument('module')]));
    }
}
