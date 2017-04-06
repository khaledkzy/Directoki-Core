<?php

namespace DirectokiBundle;



/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class ImportCSVLineResult
{


    protected $debugOutput;

    function __construct( $debugOutput ) {
        $this->debugOutput = $debugOutput;
    }

    /**
     * @return mixed
     */
    public function getDebugOutput() {
        return $this->debugOutput;
    }

}

