<?php

namespace DirectokiBundle\InternalAPI\V1\Model;



/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class Record {

    protected $publicID;

    function __construct( $publicID ) {
        $this->publicID = $publicID;
    }

    /**
     * @return mixed
     */
    public function getPublicID() {
        return $this->publicID;
    }

}
