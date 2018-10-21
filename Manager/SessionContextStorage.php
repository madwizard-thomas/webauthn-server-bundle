<?php


namespace MadWizard\WebAuthnBundle\Manager;

use MadWizard\WebAuthn\Server\RequestContext;
use MadWizard\WebAuthnBundle\Exception\SessionRequiredException;

use MadWizard\WebAuthnBundle\Session\ContextSessionBag;
use Symfony\Component\HttpFoundation\RequestStack;

class SessionContextStorage implements ContextStorageInterface
{
    /**
     * @var RequestStack
     */
    private $stack;

    public function __construct(RequestStack $stack)
    {
        $this->stack = $stack;
    }

    public function addContext(RequestContext $context) : string
    {
        return $this->getBag()->addContext($context);
    }

    public function getContext(string $key) : ?RequestContext
    {
        return $this->getBag()->getContext($key);
    }

    public function removeContext(string $key)
    {
        return $this->getBag()->remove($key);
    }

    private function getBag() : ContextSessionBag
    {
        $request = $this->stack->getCurrentRequest();
        if ($request === null) {
            throw new SessionRequiredException('No current request is available to get the current session.');
        }
        $session = $request->getSession();
        if ($session === null) {
            throw new SessionRequiredException('The current request has no session.');
        }
        $bag = $session->getBag(ContextSessionBag::NAME);
        /**
         * @var ContextSessionBag $bag
         */
        return $bag;
    }
}
