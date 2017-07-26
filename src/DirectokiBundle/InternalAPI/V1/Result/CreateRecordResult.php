<?php

namespace DirectokiBundle\InternalAPI\V1\Result;


/**
 * @license 3-clause BSD
 * @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class CreateRecordResult
{

    protected $success;

    protected $approved;

    protected $id;

    function __construct(
        $success = false,
        $approved = false,
        $id = null
    ) {
        $this->success = $success;
        $this->approved = $approved;
        $this->id = $id;
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

    /**
     * @return null
     */
    public function getId()
    {
        return $this->id;
    }



}
