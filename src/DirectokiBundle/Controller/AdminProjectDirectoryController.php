<?php

namespace DirectokiBundle\Controller;

use DirectokiBundle\Entity\Project;
use DirectokiBundle\Entity\RecordHasState;
use DirectokiBundle\Security\ProjectVoter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class AdminProjectDirectoryController extends Controller
{


    /** @var Project */
    protected $project;

    /** @var Directory */
    protected $directory;

    protected function build(string $projectId, string $directoryId) {
        $doctrine = $this->getDoctrine()->getManager();
        // load
        $repository = $doctrine->getRepository('DirectokiBundle:Project');
        $this->project = $repository->findOneByPublicId($projectId);
        if (!$this->project) {
            throw new  NotFoundHttpException('Not found');
        }
        $this->denyAccessUnlessGranted(ProjectVoter::ADMIN, $this->project);
        // load
        $repository = $doctrine->getRepository('DirectokiBundle:Directory');
        $this->directory = $repository->findOneBy(array('project'=>$this->project, 'publicId'=>$directoryId));
        if (!$this->directory) {
            throw new  NotFoundHttpException('Not found');
        }
    }


    public function indexAction(string $projectId, string $directoryId)
    {

        // build
        $this->build($projectId, $directoryId);
        //data

        return $this->render('DirectokiBundle:AdminProjectDirectory:index.html.twig', array(
            'project' => $this->project,
            'directory' => $this->directory,
        ));

    }

    public function statsAction(string $projectId, string $directoryId)
    {

        // build
        $this->build($projectId, $directoryId);
        //data

        $doctrine = $this->getDoctrine()->getManager();
        $repo = $doctrine->getRepository('DirectokiBundle:Record');
        $recordsPublished = count($repo->findBy(array('directory'=>$this->directory, 'cachedState'=>RecordHasState::STATE_PUBLISHED)));
        $recordsDeleted = count($repo->findBy(array('directory'=>$this->directory, 'cachedState'=>RecordHasState::STATE_DELETED)));
        $recordsDraft= count($repo->findBy(array('directory'=>$this->directory, 'cachedState'=>RecordHasState::STATE_DRAFT)));


        return $this->render('DirectokiBundle:AdminProjectDirectory:stats.html.twig', array(
            'project' => $this->project,
            'directory' => $this->directory,
            'recordsPublished' => $recordsPublished,
            'recordsDeleted' => $recordsDeleted,
            'recordsDraft' => $recordsDraft,
        ));

    }

    public function recordsAction(string $projectId, string $directoryId)
    {

        // build
        $this->build($projectId, $directoryId);
        //data

        $doctrine = $this->getDoctrine()->getManager();
        $repo = $doctrine->getRepository('DirectokiBundle:Record');
        $records = $repo->findByDirectory($this->directory);

        $fields = $doctrine->getRepository('DirectokiBundle:Field')->findForDirectory($this->directory);
        $field = count($fields) > 0 ? $fields[0] : null;
        if ($field) {
            $fieldType = $this->container->get('directoki_field_type_service')->getByField($field);
            $fieldTemplate = $fieldType->getViewTemplate($field);
            $fieldIsMultiple = $fieldType->isMultipleType($field);
        } else {
            $fieldType = null;
            $fieldTemplate = null;
            $fieldIsMultiple = null;
        }

        return $this->render('DirectokiBundle:AdminProjectDirectory:records.html.twig', array(
            'project' => $this->project,
            'directory' => $this->directory,
            'records' => $records,
            'field' => $field,
            'fieldType' => $fieldType,
            'fieldTemplate' => $fieldTemplate,
            'fieldIsMultilple' => $fieldIsMultiple,
        ));

    }


    public function recordsNeedingAttentionAction(string $projectId, string $directoryId)
    {

        // build
        $this->build($projectId, $directoryId);
        //data

        $doctrine = $this->getDoctrine()->getManager();
        $repo = $doctrine->getRepository('DirectokiBundle:Record');
        $records = $repo->getRecordsNeedingAttention($this->directory);

        $fields = $doctrine->getRepository('DirectokiBundle:Field')->findForDirectory($this->directory);
        $field = count($fields) > 0 ? $fields[0] : null;
        if ($field) {
            $fieldType = $this->container->get('directoki_field_type_service')->getByField($field);
            $fieldTemplate = $fieldType->getViewTemplate($field);
            $fieldIsMultiple = $fieldType->isMultipleType($field);
        } else {
            $fieldType = null;
            $fieldTemplate = null;
            $fieldIsMultiple = null;
        }

        return $this->render('DirectokiBundle:AdminProjectDirectory:recordsNeedingAttention.html.twig', array(
            'project' => $this->project,
            'directory' => $this->directory,
            'records' => $records,
            'field' => $field,
            'fieldType' => $fieldType,
            'fieldTemplate' => $fieldTemplate,
            'fieldIsMultilple' => $fieldIsMultiple,
        ));

    }

    public function fieldsAction(string $projectId, string $directoryId)
    {

        // build
        $this->build($projectId, $directoryId);
        //data

        $doctrine = $this->getDoctrine()->getManager();
        $repo = $doctrine->getRepository('DirectokiBundle:Field');
        $fields = $repo->findForDirectory($this->directory);

        return $this->render('DirectokiBundle:AdminProjectDirectory:fields.html.twig', array(
            'project' => $this->project,
            'directory' => $this->directory,
            'fields' => $fields,
            'fieldTypeService' => $this->container->get('directoki_field_type_service'),
        ));

    }



}
