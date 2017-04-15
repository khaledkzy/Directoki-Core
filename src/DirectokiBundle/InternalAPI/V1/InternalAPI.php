<?php

namespace DirectokiBundle\InternalAPI\V1;
use DirectokiBundle\Entity\RecordHasState;
use DirectokiBundle\InternalAPI\V1\Model\Record;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class InternalAPI {

    protected $container;

    function __construct( $container ) {
        $this->container = $container;
    }


    function getPublishedRecords($projectID, $directoryID) {

        $doctrine = $this->container->get('doctrine')->getManager();

        $project = $doctrine->getRepository('DirectokiBundle:Project')->findOneByPublicId($projectID);
        if (!$project) {
            throw new \Exception("Not Found");
        }

        $directory = $doctrine->getRepository('DirectokiBundle:Directory')->findOneBy(array('project'=>$project, 'publicId'=>$directoryID));
        if (!$directory) {
            throw new \Exception("Not Found");
        }

        $out = array();
        foreach($doctrine->getRepository('DirectokiBundle:Record')->findBy(array('directory'=>$directory,'cachedState'=>RecordHasState::STATE_PUBLISHED)) as $record) {
            $out[] = new Record($record->getPublicId());
        }

        return $out;

    }

}
