<?php


namespace MadWizard\WebAuthnBundle\Manager;

use MadWizard\WebAuthn\Server\RequestContext;

use MadWizard\WebAuthnBundle\Session\ContextSessionBag;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SessionContextStorage implements ContextStorageInterface
{
    /**
     * @var SessionInterface
     */
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
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
        $this->getBag()->remove($key);
    }

    private function getBag() : ContextSessionBag
    {
        $bag = $this->session->getBag(ContextSessionBag::NAME);
        /**
         * @var ContextSessionBag $bag
         */
        return $bag;
    }
}
