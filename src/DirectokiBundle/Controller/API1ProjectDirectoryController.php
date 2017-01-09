<?php

namespace DirectokiBundle\Controller;

use DirectokiBundle\Entity\Project;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class API1ProjectDirectoryController extends Controller
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
        // TODO check isAPIReadAllowed
        //$this->denyAccessUnlessGranted(ProjectVoter::VIEW, $this->project);
        // load
        $repository = $doctrine->getRepository('DirectokiBundle:Directory');
        $this->directory = $repository->findOneBy(array('project'=>$this->project, 'publicId'=>$directoryId));
        if (!$this->directory) {
            throw new  NotFoundHttpException('Not found');
        }
    }


    public function indexJSONAction($projectId, $directoryId)
    {

        // build
        $this->build($projectId, $directoryId);
        //data
        $out = array(
            'project'=>array(
                'id'=>$this->project->getPublicId(),
                'title'=>$this->project->getTitle(),
            ),
            'directory'=>array(
                'id'=>$this->directory->getPublicId(),
                'title'=>$this->directory->getTitle(),
            )
        );

        $response = new Response(json_encode($out));
        $response->headers->set('Content-Type', 'application/json');
        return $response;


    }

    protected  function fieldsData($projectId, $directoryId)
    {

        // build
        $this->build($projectId, $directoryId);
        //data

        $doctrine = $this->getDoctrine()->getManager();
        $repo = $doctrine->getRepository('DirectokiBundle:Field');

        $out = array(
            'project'=>array(
                'id'=>$this->project->getPublicId(),
                'title'=>$this->project->getTitle(),
            ),
            'directory'=>array(
                'id'=>$this->directory->getPublicId(),
                'title'=>$this->directory->getTitle(),
            ),
            'fields'=>array()
        );

        foreach($repo->findByDirectory($this->directory) as $field) {
            $fieldType = $this->container->get( 'directoki_field_type_service' )->getByField( $field );
            $out['fields'][$field->getPublicId()] = array(
                'id'=>$field->getPublicId(),
                'title'=>$field->getTitle(),
                'type'  => $fieldType::FIELD_TYPE_API1,
            );
        }

        return $out;

    }

    public function fieldsJSONAction($projectId, $directoryId)
    {
        $response = new Response(json_encode($this->fieldsData($projectId, $directoryId)));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }


    public function fieldsJSONPAction($projectId, $directoryId, Request $request)
    {
        $callback = $request->get('q') ? $request->get('q') : 'callback';
        $response = new Response($callback."(".json_encode($this->fieldsData($projectId, $directoryId)).");");
        $response->headers->set('Content-Type', 'application/javascript');
        return $response;
    }

    public function recordsJSONAction($projectId, $directoryId)
    {

        // build
        $this->build($projectId, $directoryId);
        //data

        $doctrine = $this->getDoctrine()->getManager();
        $repo = $doctrine->getRepository('DirectokiBundle:Record');

        $out = array(
            'project'=>array(
                'id'=>$this->project->getPublicId(),
                'title'=>$this->project->getTitle(),
            ),
            'directory'=>array(
                'id'=>$this->directory->getPublicId(),
                'title'=>$this->directory->getTitle(),
            ),
            'records'=>array()
        );

        foreach($repo->findByDirectory($this->directory) as $record) {
            $out['records'][] = array(
                'id'=>$record->getPublicId(),
            );
        }

        $response = new Response(json_encode($out));
        $response->headers->set('Content-Type', 'application/json');
        return $response;


    }



}
