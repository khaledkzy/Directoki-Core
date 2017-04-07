<?php

namespace DirectokiBundle;



/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class ImportCSVLineResult
{


    protected $debugOutput;

    protected $fieldValuesToSave;

    function __construct( $debugOutput, $fieldValuesToSave ) {
        $this->debugOutput = $debugOutput;
        $this->fieldValuesToSave = $fieldValuesToSave;
    }


    /**
     * @return string
     */
    public function getDebugOutput() {
        return $this->debugOutput;
    }

    /**
     * @return array
     */
    public function getFieldValuesToSave() {
        return $this->fieldValuesToSave;
    }



}

