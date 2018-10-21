<?php


namespace MadWizard\WebAuthnBundle\Manager;

use MadWizard\WebAuthn\Server\RequestContext;

interface ContextStorageInterface
{
    public function addContext(RequestContext $context) : string;

    public function getContext(string $key) : ?RequestContext;

    public function removeContext(string $key);
}
