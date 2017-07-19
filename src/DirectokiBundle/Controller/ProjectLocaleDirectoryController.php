<?php

namespace DirectokiBundle\Controller;

use DirectokiBundle\Entity\Project;
use DirectokiBundle\Entity\Locale;
use DirectokiBundle\Entity\RecordHasState;
use DirectokiBundle\Security\ProjectVoter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class ProjectLocaleDirectoryController extends Controller
{


    /** @var Project */
    protected $project;

    /** @var Locale */
    protected $locale;

    /** @var Directory */
    protected $directory;

    protected function build($projectId, $localeId, $directoryId) {
        $doctrine = $this->getDoctrine()->getManager();
        // load
        $repository = $doctrine->getRepository('DirectokiBundle:Project');
        $this->project = $repository->findOneByPublicId($projectId);
        if (!$this->project) {
            throw new  NotFoundHttpException('Not found');
        }
        // load
        $repository = $doctrine->getRepository('DirectokiBundle:Locale');
        $this->locale = $repository->findOneBy(array('project'=>$this->project,'publicId'=>$localeId));
        if (!$this->locale) {
            throw new  NotFoundHttpException('Not found');
        }
        // load
        $repository = $doctrine->getRepository('DirectokiBundle:Directory');
        $this->directory = $repository->findOneBy(array('project'=>$this->project, 'publicId'=>$directoryId));
        if (!$this->directory) {
            throw new  NotFoundHttpException('Not found');
        }
    }


    public function indexAction($projectId, $localeId, $directoryId)
    {

        // build
        $this->build($projectId, $localeId, $directoryId);
        //data

        return $this->render('DirectokiBundle:ProjectLocaleDirectory:index.html.twig', array(
            'project' => $this->project,
            'locale' => $this->locale,
            'directory' => $this->directory,
        ));

    }

    public function recordsAction($projectId, $localeId, $directoryId)
    {

        // build
        $this->build($projectId, $localeId, $directoryId);
        //data

        $doctrine = $this->getDoctrine()->getManager();
        $repo = $doctrine->getRepository('DirectokiBundle:Record');
        $records = $repo->findBy(array('directory'=>$this->directory,'cachedState'=>RecordHasState::STATE_PUBLISHED));

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

        return $this->render('DirectokiBundle:ProjectLocaleDirectory:records.html.twig', array(
            'project' => $this->project,
            'locale' => $this->locale,
            'directory' => $this->directory,
            'records' => $records,
            'field' => $field,
            'fieldType' => $fieldType,
            'fieldTemplate' => $fieldTemplate,
            'fieldIsMultilple' => $fieldIsMultiple,
        ));

    }

}
