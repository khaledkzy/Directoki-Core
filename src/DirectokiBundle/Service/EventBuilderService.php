<?php

namespace DirectokiBundle\Service;

use DirectokiBundle\Entity\Event;
use DirectokiBundle\Entity\Field;
use DirectokiBundle\Entity\Project;
use DirectokiBundle\Entity\User;
use DirectokiBundle\FieldType\FieldTypeBoolean;
use DirectokiBundle\FieldType\FieldTypeLatLng;
use DirectokiBundle\FieldType\FieldTypeString;
use DirectokiBundle\FieldType\FieldTypeText;
use Symfony\Component\HttpFoundation\Request;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class EventBuilderService
{




    public function build(Project $project, User $user = null, Request $request = null, $comment = null) {
        $event = new Event();
        $event->setProject($project);
        $event->setUser($user);
        $event->setComment($comment);
        if ($request) {
            $event->setIP($request->getClientIp());
            $event->setUserAgent($request->headers->get('User-Agent'));
        }
        return $event;
    }


}