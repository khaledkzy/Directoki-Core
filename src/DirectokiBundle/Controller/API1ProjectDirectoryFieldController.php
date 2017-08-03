<?php

namespace DirectokiBundle\Controller;

use DirectokiBundle\Entity\Directory;
use DirectokiBundle\Entity\Project;
use DirectokiBundle\Entity\Field;
use DirectokiBundle\FieldType\StringFieldType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class API1ProjectDirectoryFieldController extends Controller
{

    use API1TraitLocale;

    /** @var Project */
    protected $project;

    /** @var Directory */
    protected $directory;

    /** @var Field */
    protected $field;

    protected function build($projectId, $directoryId, $fieldId, Request $request) {
        $doctrine = $this->getDoctrine()->getManager();
        // load
        $repository = $doctrine->getRepository('DirectokiBundle:Project');
        $this->project = $repository->findOneByPublicId($projectId);
        if (!$this->project) {
            throw new  NotFoundHttpException('Not found');
        }
        //$this->denyAccessUnlessGranted(ProjectVoter::VIEW, $this->project);
        // TODO Check isAPIReadAllowed
        // load
        $repository = $doctrine->getRepository('DirectokiBundle:Directory');
        $this->directory = $repository->findOneBy(array('project'=>$this->project, 'publicId'=>$directoryId));
        if (!$this->directory) {
            throw new  NotFoundHttpException('Not found');
        }
        // load
        $repository = $doctrine->getRepository('DirectokiBundle:Field');
        $this->field = $repository->findOneBy(array('directory'=>$this->directory, 'publicId'=>$fieldId));
        if (!$this->field) {
            throw new  NotFoundHttpException('Not found');
        }

        $this->buildLocale($request);
    }


    protected function indexData($projectId, $directoryId, $fieldId, Request $request) {

        // build
        $this->build( $projectId, $directoryId, $fieldId, $request );
        //data


        $out = array(
            'project'   => array(
                'id'    => $this->project->getPublicId(),
                'title' => $this->project->getTitle(),
            ),
            'directory' => array(
                'id'    => $this->directory->getPublicId(),
                'title_singular' => $this->directory->getTitleSingular(),
                'title_plural' => $this->directory->getTitlePlural(),
            ),
            'field'    => array(
                'id' => $this->field->getPublicId(),
                'title' => $this->field->getTitle(),
            ),
        );

        return $out;
    }


    public function indexJSONAction($projectId, $directoryId, $fieldId, Request $request)
    {
        $response = new Response(json_encode($this->indexData($projectId, $directoryId, $fieldId, $request)));
        $response->headers->set('Content-Type', 'application/json');
        return $response;

    }

    public function indexJSONPAction($projectId, $directoryId, $fieldId, Request $request)
    {
        $callback = $request->get('q') ? $request->get('q') : 'callback';
        $response = new Response($callback."(".json_encode($this->indexData($projectId, $directoryId, $fieldId, $request)).");");
        $response->headers->set('Content-Type', 'application/javascript');
        return $response;
    }

    protected function selectValuesData($projectId, $directoryId, $fieldId, Request $request) {

        // build
        $this->build( $projectId, $directoryId, $fieldId, $request );
        //data


        $out = array(
            'project'   => array(
                'id'    => $this->project->getPublicId(),
                'title' => $this->project->getTitle(),
            ),
            'directory' => array(
                'id'    => $this->directory->getPublicId(),
                'title_singular' => $this->directory->getTitleSingular(),
                'title_plural' => $this->directory->getTitlePlural(),
            ),
            'field'    => array(
                'id' => $this->field->getPublicId(),
                'title' => $this->field->getTitle(),
            ),
            'select_values' => array(),
        );

        $doctrine = $this->getDoctrine()->getManager();
        $repository = $doctrine->getRepository('DirectokiBundle:SelectValue');

        foreach($repository->findBy(array('field'=>$this->field)) as $selectValue) {
            $out['select_values'][] = array(
                'id' => $selectValue->getPublicId(),
                'title' => $selectValue->getTitle(),
            );
        }

        return $out;
    }


    public function selectValuesJSONAction($projectId, $directoryId, $fieldId, Request $request)
    {
        $response = new Response(json_encode($this->selectValuesData($projectId, $directoryId, $fieldId, $request)));
        $response->headers->set('Content-Type', 'application/json');
        return $response;

    }

    public function selectValuesJSONPAction($projectId, $directoryId, $fieldId, Request $request)
    {
        $callback = $request->get('q') ? $request->get('q') : 'callback';
        $response = new Response($callback."(".json_encode($this->selectValuesData($projectId, $directoryId, $fieldId, $request)).");");
        $response->headers->set('Content-Type', 'application/javascript');
        return $response;
    }



}
