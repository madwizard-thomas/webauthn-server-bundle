<?php


namespace MadWizard\WebAuthnBundle\Manager;

use MadWizard\WebAuthn\Server\RequestContext;

use MadWizard\WebAuthnBundle\Session\ContextSessionBag;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface;

class SessionContextStorage implements ContextStorageInterface
{
    /**
     * @var SessionStorageInterface
     */
    private $sessionStorage;

    public function __construct(SessionStorageInterface $sessionStorage)
    {
        $this->sessionStorage = $sessionStorage;
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
        $bag = $this->sessionStorage->getBag(ContextSessionBag::NAME);
        /**
         * @var ContextSessionBag $bag
         */
        return $bag;
    }
}
