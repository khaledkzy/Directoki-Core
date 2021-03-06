<?php

namespace DirectokiBundle\Controller;

use DirectokiBundle\Entity\Project;
use DirectokiBundle\Entity\RecordReport;
use DirectokiBundle\FieldType\StringFieldType;
use DirectokiBundle\Security\ProjectVoter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class AdminProjectDirectoryRecordReportController extends Controller
{


    /** @var Project */
    protected $project;

    /** @var Directory */
    protected $directory;

    /** @var Record */
    protected $record;

    /** @var RecordReport */
    protected $report;

    protected function build(string $projectId, string $directoryId, string $recordId, string $reportId)
    {
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
        $this->directory = $repository->findOneBy(array('project' => $this->project, 'publicId' => $directoryId));
        if (!$this->directory) {
            throw new  NotFoundHttpException('Not found');
        }
        // load
        $repository = $doctrine->getRepository('DirectokiBundle:Record');
        $this->record = $repository->findOneBy(array('directory' => $this->directory, 'publicId' => $recordId));
        if (!$this->record) {
            throw new  NotFoundHttpException('Not found');
        }
        // load
        $repository = $doctrine->getRepository('DirectokiBundle:RecordReport');
        $this->report = $repository->findOneBy(array('record' => $this->record, 'id' => $reportId));
        if (!$this->report) {
            throw new  NotFoundHttpException('Not found');
        }
    }

}

