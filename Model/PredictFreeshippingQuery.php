<?php

namespace Predict\Model;

use Predict\Model\Base\PredictFreeshippingQuery as BasePredictFreeshippingQuery;


/**
 * Skeleton subclass for performing query and update operations on the 'predict_freeshipping' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 */
class PredictFreeshippingQuery extends BasePredictFreeshippingQuery
{
    public function getLast()
    {
        return $this->orderById('desc')->findOne()->getActive();
    }
} // PredictFreeshippingQuery
