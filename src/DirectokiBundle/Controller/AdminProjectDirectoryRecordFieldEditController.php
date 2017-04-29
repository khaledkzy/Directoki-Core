<?php

namespace DirectokiBundle\Controller;
use DirectokiBundle\Action\UpdateRecordCache;
use DirectokiBundle\Entity\Event;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class AdminProjectDirectoryRecordFieldEditController extends AdminProjectDirectoryRecordFieldController
{

    protected function build($projectId, $directoryId, $recordId, $fieldId) {
        parent::build($projectId, $directoryId, $recordId, $fieldId);
        // parent function will do security
    }


    public function editAction($projectId, $directoryId, $recordId, $fieldId)
    {

        // build
        $this->build($projectId, $directoryId, $recordId, $fieldId);
        //data
        $doctrine = $this->getDoctrine()->getManager();

        $fieldType = $this->container->get('directoki_field_type_service')->getByField($this->field);

        $form = $this->createForm($fieldType->getEditFieldForm($this->field, $this->record));
        $request = $this->getRequest();
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {

                $event = $this->get('directoki_event_builder_service')->build(
                    $this->project,
                    $this->getUser(),
                    $this->getRequest(),
                    $form->get('createdComment')->getData()
                );

                $recordHasFieldValuesToSave = $fieldType->getEditFieldFormNewRecords($this->field, $this->record, $event, $form, $this->getUser(), $form->get('approve')->getData());
                // There might be nothing to save!
                if ($recordHasFieldValuesToSave) {
                    $doctrine->persist($event);
                    foreach($recordHasFieldValuesToSave as $recordHasFieldValueToSave) {
                        $doctrine->persist( $recordHasFieldValueToSave );
                    }
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


        return $this->render('DirectokiBundle:AdminProjectDirectoryRecordFieldEdit:edit.html.twig', array(
            'project' => $this->project,
            'directory' => $this->directory,
            'record' => $this->record,
            'field' => $this->field,
            'form' => $form->createView(),
        ));

    }



}
