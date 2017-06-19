<?php

namespace DirectokiBundle\Controller;

use DirectokiBundle\Entity\Project;
use DirectokiBundle\Security\ProjectVoter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class AdminProjectController extends Controller
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
        $this->denyAccessUnlessGranted(ProjectVoter::ADMIN, $this->project);
    }


    public function indexAction($projectId)
    {

        // build
        $this->build($projectId);
        //data

        $doctrine = $this->getDoctrine()->getManager();
        $repo = $doctrine->getRepository('DirectokiBundle:Directory');
        $directories = $repo->findByProject($this->project);

        return $this->render('DirectokiBundle:AdminProject:index.html.twig', array(
            'project' => $this->project,
            'directories' => $directories,
        ));

    }

    public function userAction($projectId)
    {

        // build
        $this->build($projectId);
        //data

        $doctrine = $this->getDoctrine()->getManager();
        $repo = $doctrine->getRepository('DirectokiBundle:ProjectAdmin');
        $projectAdmins = $repo->findByProject($this->project);

        return $this->render('DirectokiBundle:AdminProject:user.html.twig', array(
            'project' => $this->project,
            'projectAdmins' => $projectAdmins,
        ));

    }


    public function historyAction($projectId)
    {

        // build
        $this->build($projectId);
        //data

        $doctrine = $this->getDoctrine()->getManager();
        $repo = $doctrine->getRepository('DirectokiBundle:Event');
        $histories = $repo->findBy(array('project'=>$this->project),array('createdAt'=>'desc'),1000);

        return $this->render('DirectokiBundle:AdminProject:history.html.twig', array(
            'project' => $this->project,
            'histories' => $histories,
        ));

    }



    public function localeAction($projectId)
    {

        // build
        $this->build($projectId);
        //data

        $doctrine = $this->getDoctrine()->getManager();
        $repo = $doctrine->getRepository('DirectokiBundle:Locale');
        $locales = $repo->findBy(array('project'=>$this->project),array('title'=>'asc'));

        return $this->render('DirectokiBundle:AdminProject:locale.html.twig', array(
            'project' => $this->project,
            'locales' => $locales,
        ));

    }



}
