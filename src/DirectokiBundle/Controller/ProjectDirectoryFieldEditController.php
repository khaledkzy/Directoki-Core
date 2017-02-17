<?php

namespace DirectokiBundle\Controller;


use DirectokiBundle\Entity\SelectValue;
use DirectokiBundle\FieldType\FieldTypeMultiSelect;
use DirectokiBundle\Form\Type\SelectValueNewType;
use DirectokiBundle\Security\ProjectVoter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class ProjectDirectoryFieldEditController extends ProjectDirectoryFieldController {

    protected function build( $projectId, $directoryId, $fieldId ) {
        parent::build( $projectId, $directoryId, $fieldId );
        $this->denyAccessUnlessGranted(ProjectVoter::EDIT, $this->project);
    }


    public function newSelectValueAction($projectId, $directoryId, $fieldId)
    {

        // build
        $this->build($projectId, $directoryId, $fieldId);
        if ($this->field->getFieldType() != FieldTypeMultiSelect::FIELD_TYPE_INTERNAL) {
            throw new  NotFoundHttpException('Not found');
        }

        //data

        $doctrine = $this->getDoctrine()->getManager();


        $selectValue = new SelectValue();
        $selectValue->setField($this->field);

        $form = $this->createForm(new SelectValueNewType(), $selectValue);
        $request = $this->getRequest();
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {

                $event = $this->get('directoki_event_builder_service')->build(
                    $this->project,
                    $this->getUser(),
                    $this->getRequest(),
                    null
                );
                $doctrine->persist($event);

                $selectValue->setCreationEvent($event);
                $doctrine->persist($selectValue);

                $doctrine->flush();

                return $this->redirect($this->generateUrl('directoki_project_directory_field_select_values_list', array(
                    'projectId'=>$this->project->getPublicId(),
                    'directoryId'=>$this->directory->getPublicId(),
                    'fieldId'=>$this->field->getPublicId(),
                )));
            }
        }


        return $this->render('DirectokiBundle:ProjectDirectoryFieldEdit:newSelectValue.html.twig', array(
            'project' => $this->project,
            'directory' => $this->directory,
            'field' => $this->field,
            'form' => $form->createView(),
        ));


    }



}