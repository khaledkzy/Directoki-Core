<?php

namespace DirectokiBundle\Controller;

use DirectokiBundle\Entity\Project;
use DirectokiBundle\Security\ProjectVoter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class ProjectController extends Controller
{


    /** @var Project */
    protected $project;


    protected function build($projectId) {
        $doctrine = $this->getDoctrine()->getManager();
        // load
        $repository = $doctrine->getRepository('DirectokiBundle:Project');
        $this->project = $repository->findOneByPublicId($projectId);
        if (!$this->project) {
            throw new  NotFoundHttpException('Not found');
        }
    }

    public function indexAction($projectId)
    {

        // build
        $this->build($projectId);
        //data

        $doctrine = $this->getDoctrine()->getManager();
        $repo = $doctrine->getRepository('DirectokiBundle:Directory');
        $directories = $repo->findByProject($this->project);

        return $this->render('DirectokiBundle:Project:index.html.twig', array(
            'project' => $this->project,
            'directories' => $directories,
        ));

    }

}