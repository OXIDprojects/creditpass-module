<?php

namespace OxidProfessionalServices\CreditPassModule\Core;

use OxidProfessionalServices\CreditPassModule\Core\CreditPassAssessment;

/**
* View config data access class. Keeps most
* of getters needed for formatting various urls,
* config parameters, session information etc.
*/

class ViewConfig extends ViewConfig_parent
{
    /**
     * Get answer codes for translating to human readable text.
     *
     * @return array
     */
    public function getCreditPassAnswerCodesForLog()
    {
        $creditPassAssessment = oxNew(CreditPassAssessment::class);

        $aAnswerCodes = $creditPassAssessment->getAnswerCodes();
        // include description for empty or no answer code
        $aAnswerCodes[''] = 'OECREDITPASS_LOG_LIST_EMPTY';

        return $aAnswerCodes;
    }
}
