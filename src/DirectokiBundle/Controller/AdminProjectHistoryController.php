<?php

namespace DirectokiBundle\Controller;

use DirectokiBundle\Entity\Project;
use DirectokiBundle\Entity\Event;
use DirectokiBundle\Security\ProjectVoter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class AdminProjectHistoryController extends Controller
{


    /** @var Project */
    protected $project;

    /** @var Event */
    protected $event;

    protected function build($projectId, $historyId) {
        $doctrine = $this->getDoctrine()->getManager();
        // load
        $repository = $doctrine->getRepository('DirectokiBundle:Project');
        $this->project = $repository->findOneByPublicId($projectId);
        if (!$this->project) {
            throw new  NotFoundHttpException('Not found');
        }
        $this->denyAccessUnlessGranted(ProjectVoter::ADMIN, $this->project);
        // load
        $repository = $doctrine->getRepository('DirectokiBundle:Event');
        $this->event = $repository->findOneBy(array('project'=>$this->project, 'id'=>$historyId));
        if (!$this->event) {
            throw new  NotFoundHttpException('Not found');
        }
    }


    public function indexAction($projectId, $historyId)
    {

        // build
        $this->build($projectId, $historyId);
        //data

        return $this->render('DirectokiBundle:AdminProjectHistory:index.html.twig', array(
            'project' => $this->project,
            'event' => $this->event,
        ));

    }



}
