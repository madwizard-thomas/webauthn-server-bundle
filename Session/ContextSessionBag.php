<?php


namespace MadWizard\WebAuthnBundle\Session;

use MadWizard\WebAuthn\Server\RequestContext;
use Symfony\Component\HttpFoundation\Session\SessionBagInterface;

class ContextSessionBag implements SessionBagInterface
{
    public const NAME = 'madwizard_webauthn_context';

    /**
     * @var string
     */
    private $storageKey;

    /** @var array */
    private $map;

    /**
     * @param string $storageKey The key used to store flashes in the session
     */
    public function __construct(string $storageKey = 'madwizard_webauthn_context')
    {
        $this->storageKey = $storageKey;
        $this->map = [];
    }

    public function getName()
    {
        return self::NAME;
    }

    public function initialize(array &$array)
    {
        $this->map = &$array;
    }

    public function getStorageKey()
    {
        return $this->storageKey;
    }

    public function clear()
    {
        $map = $this->map;
        $this->map = [];
        return $map;
    }

    // TODO: max number of items - also expiration

    /**
     * @param string $key
     * @return RequestContext|null
     */
    public function getContext(string $key) : ?RequestContext
    {
        return $this->map[$key] ?? null;
    }

    public function remove(string $key)
    {
        if (isset($this->map[$key])) {
            unset($this->map[$key]);
        }
    }

    public function addContext(RequestContext $context) : string
    {
        $key = \bin2hex(\random_bytes(64));
        $this->map[$key] = $context;
        return $key;
    }
}
