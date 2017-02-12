<?php

namespace DirectokiBundle\Controller;

use DirectokiBundle\Entity\Event;
use DirectokiBundle\Entity\Project;
use DirectokiBundle\Entity\Record;
use DirectokiBundle\Entity\RecordHasState;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class API1ProjectDirectoryEditController extends API1ProjectDirectoryController
{
    protected function build( $projectId, $directoryId ) {
        parent::build( $projectId, $directoryId );
        // TODO check isAPIModeratedEditAllowed
    }


    public function newRecordData($projectId, $directoryId, ParameterBag $parameterBag, Request $request) {

        // build
        $this->build($projectId, $directoryId);
        //data

        $doctrine = $this->getDoctrine()->getManager();


        $fields = $doctrine->getRepository( 'DirectokiBundle:Field' )->findForDirectory( $this->directory );

        $fieldDataToSave = array();
        foreach ( $fields as $field ) {

            $fieldType = $this->container->get( 'directoki_field_type_service' )->getByField( $field );

            $fieldDataToSave = array_merge($fieldDataToSave, $fieldType->processAPI1Record($field, null, $parameterBag));

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
                    $this->get('logger')->error('A new record on project '.$this->project->getPublicId().' directory '.$this->directory->getPublicId().' had an email address we did not recognise: ' . $email);
                }
            }
            $doctrine->persist($event);


            $record = new Record();
            $record->setDirectory($this->directory);
            $record->setCreationEvent($event);
            $record->setCachedState(RecordHasState::STATE_DRAFT);
            $doctrine->persist($record);

            $recordHasState = new RecordHasState();
            $recordHasState->setRecord($record);
            $recordHasState->setState(RecordHasState::STATE_PUBLISHED);
            $recordHasState->setCreationEvent($event);
            $doctrine->persist($recordHasState);

            foreach($fieldDataToSave as $entityToSave) {
                $entityToSave->setRecord($record);
                $entityToSave->setCreationEvent($event);
                $doctrine->persist($entityToSave);
            }

            $doctrine->flush();

            return array('id'=>$record->getPublicId());
        } else {

            return array();
        }

    }

    public function newRecordJSONAction($projectId, $directoryId, Request $request) {
        $response = new Response(json_encode($this->newRecordData($projectId, $directoryId, $request->request, $request)));
        $response->headers->set('Content-Type', 'application/json');
        return $response;

    }

    public function newRecordJSONPAction($projectId, $directoryId, Request $request) {
        $callback = $request->get('q') ? $request->get('q') : 'callback';
        $response = new Response($callback."(".json_encode($this->newRecordData($projectId, $directoryId, $request->query, $request)).");");
        $response->headers->set('Content-Type', 'application/javascript');
        return $response;
    }

}
