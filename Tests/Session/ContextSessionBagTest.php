<?php

namespace MadWizard\WebAuthnBundle\Tests\Session;

use MadWizard\WebAuthn\Server\RequestContext;
use MadWizard\WebAuthnBundle\Session\ContextSessionBag;
use PHPUnit\Framework\TestCase;

class ContextSessionBagTest extends TestCase
{
    /**
     * @var ContextSessionBag
     */
    private $bag;

    private $linkedArr = [];

    protected function setUp()
    {
        $this->bag = new ContextSessionBag();
        $this->bag->initialize($this->linkedArr);
    }

    public function testGetStorageKey()
    {
        $this->assertSame('madwizard_webauthn_context', $this->bag->getStorageKey());

        $newBag = new ContextSessionBag('aaa');
        $this->assertSame('aaa', $newBag->getStorageKey());
    }

    public function testGetContext()
    {
        /**
         * @var RequestContext $context
         */
        $context = $this->getMockBuilder(RequestContext::class)->getMock();
        $name = $this->bag->addContext($context);

        $this->assertArrayHasKey($name, $this->linkedArr);
        $this->assertInstanceOf(RequestContext::class, $this->bag->getContext($name));
    }

    public function testGetName()
    {
        $this->assertSame(ContextSessionBag::NAME, $this->bag->getName());
    }
}
