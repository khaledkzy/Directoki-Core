<?php

namespace DirectokiBundle\Controller;

use DirectokiBundle\Entity\Project;
use DirectokiBundle\Entity\RecordReport;
use DirectokiBundle\FieldType\StringFieldType;
use DirectokiBundle\Form\Type\RecordReportResolveType;
use DirectokiBundle\Security\ProjectVoter;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class AdminProjectDirectoryRecordReportEditController extends AdminProjectDirectoryRecordReportController
{
    protected function build(string $projectId, string $directoryId, string $recordId, string $reportId)
    {
        parent::build($projectId, $directoryId, $recordId, $reportId);
        $this->denyAccessUnlessGranted(ProjectVoter::ADMIN, $this->project);
        if ($this->container->getParameter('directoki.read_only')) {
            throw new HttpException(503, 'Directoki is in Read Only mode.');
        }
    }

    public function resolveAction(string $projectId, string $directoryId, string $recordId, string $reportId, Request $request) {



        // build
        $this->build($projectId, $directoryId, $recordId, $reportId);
        //data

        $doctrine = $this->getDoctrine()->getManager();



        $form = $this->createForm(new RecordReportResolveType());
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {

                $event = $this->get('directoki_event_builder_service')->build(
                    $this->project,
                    $this->getUser(),
                    $request,
                    $form->get('comment')->getData()
                );
                $doctrine->persist($event);

                $this->report->setResolvedAt(new \DateTime());
                $this->report->setResolutionEvent($event);
                $doctrine->persist($this->report);

                $doctrine->flush();

                return $this->redirect($this->generateUrl('directoki_admin_project_directory_record_show', array(
                    'projectId'=>$this->project->getPublicId(),
                    'directoryId'=>$this->directory->getPublicId(),
                    'recordId'=>$this->record->getPublicId(),
                )));
            }
        }


        return $this->render('DirectokiBundle:AdminProjectDirectoryRecordReportEdit:resolve.html.twig', array(
            'project' => $this->project,
            'directory' => $this->directory,
            'record' => $this->record,
            'report' => $this->report,
            'form' => $form->createView(),
        ));


    }


}
