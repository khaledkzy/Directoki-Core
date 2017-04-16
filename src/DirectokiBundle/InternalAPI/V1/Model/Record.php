<?php

namespace DirectokiBundle\InternalAPI\V1\Model;



/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class Record {

    protected $publicID;

    protected $fields;

    function __construct( $publicID, $fields = array() ) {
        $this->publicID = $publicID;
        $this->fields = $fields;
    }

    /**
     * @return mixed
     */
    public function getPublicID() {
        return $this->publicID;
    }

    public function getFieldValue($pubicId) {
        return isset($this->fields[$pubicId]) ? $this->fields[$pubicId] : null;
    }

}
