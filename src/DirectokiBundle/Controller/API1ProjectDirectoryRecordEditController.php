<?php

namespace DirectokiBundle\Controller;

use DirectokiBundle\Entity\Event;
use DirectokiBundle\Entity\Project;
use DirectokiBundle\Entity\RecordReport;
use DirectokiBundle\FieldType\StringFieldType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class API1ProjectDirectoryRecordEditController extends API1ProjectDirectoryRecordController
{



    protected function build($projectId, $directoryId, $recordId) {
        parent::build($projectId, $directoryId, $recordId);
        // TODO check isAPIModeratedEditAllowed

    }


    protected function editData($projectId, $directoryId, $recordId, ParameterBag $parameterBag, Request $request) {

        // build
        $this->build( $projectId, $directoryId, $recordId );
        //data
        $doctrine = $this->getDoctrine()->getManager();


        $fields = $doctrine->getRepository( 'DirectokiBundle:Field' )->findForDirectory( $this->directory );

        $fieldDataToSave = array();
        foreach ( $fields as $field ) {

            $fieldType = $this->container->get( 'directoki_field_type_service' )->getByField( $field );

            $fieldDataToSave = array_merge($fieldDataToSave, $fieldType->processAPI1Record($field, $this->record, $parameterBag));

        }

        if ($fieldDataToSave) {

            $event = $this->get('directoki_event_builder_service')->build(
                $this->project,
                $this->getUser(),
                $request,
                $parameterBag->get('comment')
            );
            $event->setAPIVersion(1);
            $email = trim($parameterBag->get('email'));
            if ($email) {
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $event->setContact( $doctrine->getRepository( 'DirectokiBundle:Contact' )->findOrCreateByEmail($this->project, $email));
                } else {
                    $this->get('logger')->error('An edit on project '.$this->project->getPublicId().' directory '.$this->directory->getPublicId().' record '.$this->record->getPublicId().' had an email address we did not recognise: ' . $email);
                }
            }
            $doctrine->persist($event);

            foreach($fieldDataToSave as $entityToSave) {
                $entityToSave->setCreationEvent($event);
                $doctrine->persist($entityToSave);
            }

            $doctrine->flush();

            return array();

        } else {
            return array();
        }

    }


    public function editJSONAction($projectId, $directoryId, $recordId, Request $request)
    {
        $response = new Response(json_encode($this->editData($projectId, $directoryId, $recordId, $request->request, $request)));
        $response->headers->set('Content-Type', 'application/json');
        return $response;

    }

    public function editJSONPAction($projectId, $directoryId, $recordId, Request $request)
    {
        $callback = $request->get('q') ? $request->get('q') : 'callback';
        $response = new Response($callback."(".json_encode($this->editData($projectId, $directoryId, $recordId, $request->query, $request)).");");
        $response->headers->set('Content-Type', 'application/javascript');
        return $response;
    }



    protected function newReportData($projectId, $directoryId, $recordId, ParameterBag $parameterBag, Request $request) {

        // build
        $this->build( $projectId, $directoryId, $recordId );
        //data
        $doctrine = $this->getDoctrine()->getManager();
        $out = array();
        
        if ($parameterBag->get('description')) {

            $event = $this->get('directoki_event_builder_service')->build(
                $this->project,
                $this->getUser(),
                $request,
                null
            );
            $event->setAPIVersion(1);
            $email = trim($parameterBag->get('email'));
            if ($email) {
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $event->setContact( $doctrine->getRepository( 'DirectokiBundle:Contact' )->findOrCreateByEmail($this->project, $email));
                } else {
                    $this->get('logger')->error('A new report on project '.$this->project->getPublicId().' directory '.$this->directory->getPublicId().' record '.$this->record->getPublicId().' had an email address we did not recognise: ' . $email);
                }
            }
            $doctrine->persist($event);

            $recordReport = new RecordReport();
            $recordReport->setCreationEvent($event);
            $recordReport->setRecord($this->record);
            $recordReport->setDescription($parameterBag->get('description'));
            $doctrine->persist($recordReport);

            $doctrine->flush();

        }

        return $out;
    }

    // TODO newReportJSONAction

    public function newReportJSONPAction($projectId, $directoryId, $recordId, Request $request) {
        $callback = $request->get('q') ? $request->get('q') : 'callback';
        $response = new Response($callback."(".json_encode($this->newReportData($projectId, $directoryId, $recordId, $request->query, $request)).");");
        $response->headers->set('Content-Type', 'application/javascript');
        return $response;

    }



}
