<?php

namespace DirectokiBundle\Controller;

use DirectokiBundle\Entity\Project;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class API1ProjectController extends Controller
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

        // TODO check isAPIReadAllowed
        //$this->denyAccessUnlessGranted(ProjectVoter::VIEW, $this->project);
    }


    public function indexJSONAction($projectId)
    {

        // build
        $this->build($projectId);
        //data

        $doctrine = $this->getDoctrine()->getManager();
        $repo = $doctrine->getRepository('DirectokiBundle:Directory');
        $directories = $repo->findByProject($this->project);

        $out = array(
            'project'=>array(
                'id'=>$this->project->getPublicId(),
                'title'=>$this->project->getTitle(),
            )
        );

        $response = new Response(json_encode($out));
        $response->headers->set('Content-Type', 'application/json');
        return $response;

    }



    public function directoriesJSONAction($projectId)
    {

        // build
        $this->build($projectId);
        //data

        $doctrine = $this->getDoctrine()->getManager();
        $repo = $doctrine->getRepository('DirectokiBundle:Directory');

        $out = array(
            'project'=>array(
                'id'=>$this->project->getPublicId(),
                'title'=>$this->project->getTitle(),
            ),
            'directories'=>array(),
        );

        foreach($repo->findByProject($this->project) as $directory) {
            $out['directories'][] = array(
                'id' => $directory->getPublicId(),
                'title' => $directory->getTitle(),
            );
        }

        $response = new Response(json_encode($out));
        $response->headers->set('Content-Type', 'application/json');
        return $response;

    }



}
