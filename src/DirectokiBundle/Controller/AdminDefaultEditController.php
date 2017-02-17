<?php

namespace DirectokiBundle\Controller;

use DirectokiBundle\Entity\Project;
use DirectokiBundle\Form\Type\ProjectNewType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class AdminDefaultEditController extends Controller
{




    public function newProjectAction()
    {

        $doctrine = $this->getDoctrine()->getManager();


        $project = new Project();
        $project->setOwner($this->getUser());

        $form = $this->createForm(new ProjectNewType(), $project);
        $request = $this->getRequest();
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $doctrine->persist($project);
                $doctrine->flush();
                return $this->redirect($this->generateUrl('directoki_admin_project_show', array(
                    'projectId'=>$project->getPublicId(),
                )));
            }
        }




        return $this->render('DirectokiBundle:AdminDefaultEdit:newProject.html.twig', array(
            'form' => $form->createView(),
        ));
    }

}
