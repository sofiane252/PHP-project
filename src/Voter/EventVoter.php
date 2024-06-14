<?php

namespace App\Security;

use App\Entity\Event;
use Symfony\Bundle\SecurityBundle\Security as SecurityBundleSecurity;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class EventVoter extends Voter
{
    const VIEW = 'view_event';
    const EDIT = 'edit';
    const DELETE = 'delete';

    private $security;

    public function __construct(SecurityBundleSecurity $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE])
            && $subject instanceof Event;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        /** @var Event $event */
        $event = $subject;
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($event, $user);
            case self::EDIT:
            case self::DELETE:
                return $this->canEditOrDelete($event, $user);
        }

        return false;
    }

    private function canView(Event $event, UserInterface $user): bool
    {
        if ($event->isPublique()) {
            return true;
        }

        // Vérifier si l'utilisateur a le rôle 'ROLE_USER'
        return $this->security->isGranted('ROLE_USER');
    }

    private function canEditOrDelete(Event $event, UserInterface $user): bool
    {
        // Seul le créateur peut modifier ou supprimer l'événement
        return $event->getCreator() === $user;
    }
}
