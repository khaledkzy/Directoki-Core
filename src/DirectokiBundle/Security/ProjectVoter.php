<?php

namespace DirectokiBundle\Security;

use DirectokiBundle\Entity\Project;
use DirectokiBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class ProjectVoter extends Voter {
    // these strings are just invented: you can use anything
    const VIEW = 'view';
    const EDIT = 'edit';


    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, array(self::VIEW, self::EDIT))) {
            return false;
        }

        // only vote on Post objects inside this voter
        if (!$subject instanceof Project) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        switch ($attribute) {
            case self::VIEW:
                // Owner definitely can
                if ($user instanceof User && $user == $subject->getOwner()) {
                    return true;
                }

                // Others can not.
                return false;
            case self::EDIT:
                // Owner definitely can
                if ($user instanceof User && $user == $subject->getOwner()) {
                    return true;
                }

                // Others can not.
                return false;
        }

        throw new \LogicException('This code should not be reached!');
    }



}

