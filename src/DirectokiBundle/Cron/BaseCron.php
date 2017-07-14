<?php

namespace DirectokiBundle\Cron;

use DirectokiBundle\Entity\Record;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
abstract class BaseCron
{

    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    function runForRecord(Record $record) {

    }

}
