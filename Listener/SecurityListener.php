<?php

namespace Bankiru\Api\Rpc\Listener;

use Bankiru\Api\Rpc\Event\FilterControllerEvent;
use Bankiru\Api\Rpc\Exception\AccessDeniedException;
use Bankiru\Api\Rpc\Routing\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

final class SecurityListener
{
    /** @var AuthorizationChecker */
    private $authenticator;

    public function onFilterController(FilterControllerEvent $event)
    {
        /** @var Route $route */
        $route = $event->getRequest()->getAttributes()->get('_route');

        if (null === $route) {
            return;
        }

        $roles = $route->getOption('_roles', null);
        if (is_array($roles) && !$this->filterByRoles($roles)) {
            throw AccessDeniedException::rolesNeeded($roles);
        }
    }

    private function filterByRoles(array $roles)
    {
        foreach ($roles as $role) {
            if (!$this->authenticator->isGranted($role)) {
                return false;
            }
        }

        return true;
    }
}
