<?php

/*
 * Copyright (c) 2010-2017 Fabien Potencier
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to t * *he following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 */

namespace Bankiru\Api\Rpc\Listener;

use Bankiru\Api\Rpc\Event\FilterControllerEvent;
use Bankiru\Api\Rpc\Http\RequestInterface;
use Bankiru\Api\Rpc\RpcEvents;
use Sensio\Bundle\FrameworkExtraBundle\Security\ExpressionLanguage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

/**
 * SecurityListener handles security restrictions on controllers.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
final class SecurityListener implements EventSubscriberInterface
{
    private $tokenStorage;
    private $authChecker;
    private $language;
    private $trustResolver;
    private $roleHierarchy;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authChecker,
        ExpressionLanguage $language = null,
        AuthenticationTrustResolverInterface $trustResolver = null,
        RoleHierarchyInterface $roleHierarchy = null
    ) {
        $this->tokenStorage  = $tokenStorage;
        $this->authChecker   = $authChecker;
        $this->language      = $language;
        $this->trustResolver = $trustResolver;
        $this->roleHierarchy = $roleHierarchy;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        $request = $event->getRequest();
        if (!$configuration = $request->getAttributes()->get('_security')) {
            return;
        }

        if (null === $this->tokenStorage || null === $this->trustResolver) {
            throw new \LogicException('To use the @Security tag, you need to install the Symfony Security bundle.');
        }

        if (null === $this->tokenStorage->getToken()) {
            throw new \LogicException('To use the @Security tag, your controller needs to be behind a firewall.');
        }

        if (null === $this->language) {
            throw new \LogicException(
                'To use the @Security tag, you need to use the Security component 2.4 or newer and install the ExpressionLanguage component.'
            );
        }

        if (!$this->language->evaluate($configuration->getExpression(), $this->getVariables($request))) {
            throw new AccessDeniedException(sprintf('Expression "%s" denied access.', $configuration->getExpression()));
        }
    }

    // code should be sync with Symfony\Component\Security\Core\Authorization\Voter\ExpressionVoter

    public static function getSubscribedEvents()
    {
        return [RpcEvents::CONTROLLER => ['onKernelController', -255]];
    }

    private function getVariables(RequestInterface $request)
    {
        $token = $this->tokenStorage->getToken();

        if (null !== $this->roleHierarchy) {
            $roles = $this->roleHierarchy->getReachableRoles($token->getRoles());
        } else {
            $roles = $token->getRoles();
        }

        $variables = [
            'token'          => $token,
            'user'           => $token->getUser(),
            'object'         => $request,
            'request'        => $request,
            'roles'          => array_map(
                function ($role) {
                    return $role->getRole();
                },
                $roles
            ),
            'trust_resolver' => $this->trustResolver,
            // needed for the is_granted expression function
            'auth_checker'   => $this->authChecker,
        ];

        // controller variables should also be accessible
        return array_merge($request->getAttributes()->all(), $variables);
    }
}
