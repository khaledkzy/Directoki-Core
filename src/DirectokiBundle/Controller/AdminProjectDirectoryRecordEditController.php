<?php

namespace DirectokiBundle\Controller;

use DirectokiBundle\Action\UpdateRecordCache;
use DirectokiBundle\Entity\Event;
use DirectokiBundle\Entity\Project;
use DirectokiBundle\Entity\RecordHasState;
use DirectokiBundle\Entity\RecordNote;
use DirectokiBundle\FieldType\StringFieldType;
use DirectokiBundle\Form\Type\RecordEditStateType;
use DirectokiBundle\Form\Type\RecordNoteNewType;
use DirectokiBundle\Security\ProjectVoter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class AdminProjectDirectoryRecordEditController extends AdminProjectDirectoryRecordController
{

    protected function build($projectId, $directoryId, $recordId) {
        parent::build($projectId, $directoryId, $recordId);
        $this->denyAccessUnlessGranted(ProjectVoter::EDIT, $this->project);
    }

    public function moderateAction($projectId, $directoryId, $recordId, Request $request)
    {

        // build
        $this->build($projectId, $directoryId, $recordId);
        //data
        $doctrine = $this->getDoctrine()->getManager();

        $fields = $doctrine->getRepository('DirectokiBundle:Field')->findForDirectory($this->directory);


        // Save???
        if ($request->getMethod() == "POST") {

            $event = $this->get('directoki_event_builder_service')->build(
                $this->project,
                $this->getUser(),
                $this->getRequest(),
                null
            );
            $anythingToSave = false;

            foreach($fields as $field) {
                $fieldType = $this->container->get('directoki_field_type_service')->getByField($field);
                foreach($fieldType->getFieldValuesToModerate($field, $this->record) as $fieldValue) {
                    $key = "field_". $field->getPublicId(). "_". $fieldValue->getId();
                    if ($request->request->get($key) == 'approve') {
                        $fieldValue->setApprovedAt(new \DateTime());
                        $fieldValue->setApprovalEvent($event);
                        $doctrine->persist($fieldValue);
                        $anythingToSave = true;

                    } else if ($request->request->get($key) == 'reject') {
                        $fieldValue->setRefusedAt(new \DateTime());
                        $fieldValue->setRefusalEvent($event);
                        $doctrine->persist($fieldValue);
                        $anythingToSave = true;
                    }
                }
                foreach($fieldType->getModerationsNeeded($field, $this->record) as $moderationNeeded) {
                    $key = "field_". $field->getPublicId(). "_". $moderationNeeded->getFieldValue()->getId();
                    if ($request->request->get($key) == 'approve') {
                        $doctrine->persist($moderationNeeded->approve($event));
                        $anythingToSave = true;
                    } else if ($request->request->get($key) == 'reject') {
                        $doctrine->persist($moderationNeeded->reject($event));
                        $anythingToSave = true;
                    }
                }
            }

            foreach($doctrine->getRepository('DirectokiBundle:RecordHasState')->findUnmoderatedForRecord($this->record) as $recordHasState) {
                $key = "state_".$recordHasState->getId();
                if ($request->request->get($key) == 'approve') {
                    $recordHasState->setApprovedAt(new \DateTime());
                    $recordHasState->setApprovalEvent($event);
                    $doctrine->persist($recordHasState);
                    $anythingToSave = true;

                } else if ($request->request->get($key) == 'reject') {
                    $recordHasState->setRefusedAt(new \DateTime());
                    $recordHasState->setRefusalEvent($event);
                    $doctrine->persist($recordHasState);
                    $anythingToSave = true;
                }
            }

            if ($anythingToSave) {
                $doctrine->persist($event);
                $doctrine->flush();
                $updateRecordCacheAction = new UpdateRecordCache($this->container);
                $updateRecordCacheAction->go($this->record);
                return $this->redirect($this->generateUrl('directoki_admin_project_directory_record_show', array(
                    'projectId'=>$this->project->getPublicId(),
                    'directoryId'=>$this->directory->getPublicId(),
                    'recordId'=>$this->record->getPublicId(),
                )));
            }

        }


        // Load
        $fieldValues = array();
        $fieldModerationsNeeded = array();
        foreach($fields as $field) {
            $fieldType = $this->container->get('directoki_field_type_service')->getByField($field);
            $fieldValues[$field->getPublicId()] = $fieldType->getFieldValuesToModerate($field, $this->record);
            $fieldModerationsNeeded[$field->getPublicId()] = $fieldType->getModerationsNeeded($field, $this->record);
        }

        $recordHasStates = $doctrine->getRepository('DirectokiBundle:RecordHasState')->findUnmoderatedForRecord($this->record);

        // Render!
        return $this->render('DirectokiBundle:AdminProjectDirectoryRecordEdit:moderate.html.twig', array(
            'project' => $this->project,
            'directory' => $this->directory,
            'record' => $this->record,
            'fields' => $fields,
            'fieldValues' => $fieldValues,
            'fieldModerationsNeeded' => $fieldModerationsNeeded,
            'fieldTypeService' => $this->container->get('directoki_field_type_service'),
            'recordHasStates' => $recordHasStates,
        ));

    }

    public function newNoteAction($projectId, $directoryId, $recordId, Request $request) {

        // build
        $this->build($projectId, $directoryId, $recordId);
        //data
        $doctrine = $this->getDoctrine()->getManager();


        $note = new RecordNote();
        $note->setRecord($this->record);
        $note->setCreatedBy($this->getUser());

        $form = $this->createForm(new RecordNoteNewType(), $note);
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $doctrine->persist($note);
                $doctrine->flush();
                return $this->redirect($this->generateUrl('directoki_admin_project_directory_record_show', array(
                    'projectId'=>$this->project->getPublicId(),
                    'directoryId'=>$this->directory->getPublicId(),
                    'recordId'=>$this->record->getPublicId(),
                )));
            }
        }




        return $this->render('DirectokiBundle:AdminProjectDirectoryRecordEdit:newNote.html.twig', array(
            'project' => $this->project,
            'directory' => $this->directory,
            'record' => $this->record,
            'form' => $form->createView(),
        ));

    }

    public function editStateAction($projectId, $directoryId, $recordId, Request $request) {

        // build
        $this->build($projectId, $directoryId, $recordId);
        //data
        $doctrine = $this->getDoctrine()->getManager();


        $currentStateRecord = $doctrine->getRepository('DirectokiBundle:RecordHasState')->getLatestStateForRecord($this->record);

        $form = $this->createForm(new RecordEditStateType($currentStateRecord));
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {


                if ($currentStateRecord->getState() != $form->get('state')->getData()) {

                    $event = $this->get('directoki_event_builder_service')->build(
                        $this->project,
                        $this->getUser(),
                        $this->getRequest(),
                        $form->get('createdComment')->getData()
                    );
                    $doctrine->persist($event);

                    $recordHasState = new RecordHasState();
                    $recordHasState->setRecord($this->record);
                    $recordHasState->setState($form->get('state')->getData());
                    $recordHasState->setCreationEvent($event);
                    if ($form->get('approve')->getData()) {
                        $recordHasState->setApprovedAt(new \DateTime());
                        $recordHasState->setApprovalEvent($event);
                    }
                    $doctrine->persist($recordHasState);

                    $doctrine->flush();

                    $updateRecordCacheAction = new UpdateRecordCache($this->container);
                    $updateRecordCacheAction->go($this->record);
                }


                return $this->redirect($this->generateUrl('directoki_admin_project_directory_record_show', array(
                    'projectId'=>$this->project->getPublicId(),
                    'directoryId'=>$this->directory->getPublicId(),
                    'recordId'=>$this->record->getPublicId(),
                )));
            }
        }




        return $this->render('DirectokiBundle:AdminProjectDirectoryRecordEdit:editState.html.twig', array(
            'project' => $this->project,
            'directory' => $this->directory,
            'record' => $this->record,
            'form' => $form->createView(),
        ));

    }

}
