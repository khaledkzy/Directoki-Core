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
class AdminProjectDirectoryController extends Controller
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
        $this->denyAccessUnlessGranted(ProjectVoter::ADMIN, $this->project);
        // load
        $repository = $doctrine->getRepository('DirectokiBundle:Directory');
        $this->directory = $repository->findOneBy(array('project'=>$this->project, 'publicId'=>$directoryId));
        if (!$this->directory) {
            throw new  NotFoundHttpException('Not found');
        }
    }


    public function indexAction($projectId, $directoryId)
    {

        // build
        $this->build($projectId, $directoryId);
        //data

        return $this->render('DirectokiBundle:AdminProjectDirectory:index.html.twig', array(
            'project' => $this->project,
            'directory' => $this->directory,
        ));

    }

    public function recordsAction($projectId, $directoryId)
    {

        // build
        $this->build($projectId, $directoryId);
        //data

        $doctrine = $this->getDoctrine()->getManager();
        $repo = $doctrine->getRepository('DirectokiBundle:Record');
        $records = $repo->findByDirectory($this->directory);

        return $this->render('DirectokiBundle:AdminProjectDirectory:records.html.twig', array(
            'project' => $this->project,
            'directory' => $this->directory,
            'records' => $records,
        ));

    }


    public function recordsNeedingAttentionAction($projectId, $directoryId)
    {

        // build
        $this->build($projectId, $directoryId);
        //data

        $doctrine = $this->getDoctrine()->getManager();
        $repo = $doctrine->getRepository('DirectokiBundle:Record');
        $records = $repo->getRecordsNeedingAttention($this->directory);

        return $this->render('DirectokiBundle:AdminProjectDirectory:recordsNeedingAttention.html.twig', array(
            'project' => $this->project,
            'directory' => $this->directory,
            'records' => $records,
        ));

    }

    public function fieldsAction($projectId, $directoryId)
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
