<?php

namespace DirectokiBundle\InternalAPI\V1\Result;



/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class EditRecordResult
{

    protected $success;

    protected $approved;

    function __construct(
        $success = false,
        $approved = false
    ) {
        $this->success = $success;
        $this->approved = $approved;
    }

    /**
     * @return mixed
     */
    public function getSuccess()
    {
        return $this->success;
    }

    /**
     * @return boolean
     */
    public function isApproved()
    {
        return $this->approved;
    }



}
