<?php

namespace DirectokiBundle\Action;

use DirectokiBundle\Entity\Project;
use DirectokiBundle\Entity\Record;
use DirectokiBundle\Form\Type\ProjectNewType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class UpdateRecordCache
{


    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }


    public function go(Record $record) {

        $doctrine = $this->container->get('doctrine')->getManager();

        $record->setCachedState($doctrine->getRepository('DirectokiBundle:RecordHasState')->getLatestStateForRecord($record)->getState());

        $doctrine->persist($record);
        $doctrine->flush($record);

    }



}
