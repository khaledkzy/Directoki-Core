<?php

namespace DirectokiBundle\Controller;

use DirectokiBundle\Entity\Project;
use DirectokiBundle\Entity\RecordHasState;
use DirectokiBundle\Security\ProjectVoter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class ProjectDirectoryController extends Controller
{


    /** @var Project */
    protected $project;

    /** @var Directory */
    protected $directory;

    protected function build($projectId, $directoryId) {
        $doctrine = $this->getDoctrine()->getManager();
        // load
        $repository = $doctrine->getRepository('DirectokiBundle:Project');
        $this->project = $repository->findOneByPublicId($projectId);
        if (!$this->project) {
            throw new  NotFoundHttpException('Not found');
        }
        // load
        $repository = $doctrine->getRepository('DirectokiBundle:Directory');
        $this->directory = $repository->findOneBy(array('project'=>$this->project, 'publicId'=>$directoryId));
        if (!$this->directory) {
            throw new  NotFoundHttpException('Not found');
        }
    }


    public function indexAction($projectId, $directoryId)
    {

        // build
        $this->build($projectId, $directoryId);
        //data

        return $this->render('DirectokiBundle:ProjectDirectory:index.html.twig', array(
            'project' => $this->project,
            'directory' => $this->directory,
        ));

    }

    public function recordsAction($projectId, $directoryId)
    {

        // build
        $this->build($projectId, $directoryId);
        //data

        $doctrine = $this->getDoctrine()->getManager();
        $repo = $doctrine->getRepository('DirectokiBundle:Record');
        $records = $repo->findBy(array('directory'=>$this->directory,'cachedState'=>RecordHasState::STATE_PUBLISHED));

        return $this->render('DirectokiBundle:ProjectDirectory:records.html.twig', array(
            'project' => $this->project,
            'directory' => $this->directory,
            'records' => $records,
        ));

    }

}
