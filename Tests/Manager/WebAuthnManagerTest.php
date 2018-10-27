<?php


namespace MadWizard\WebAuthnBundle\Tests\Manager;

use MadWizard\WebAuthn\Format\ByteBuffer;
use MadWizard\WebAuthn\Server\Registration\RegistrationOptions;
use MadWizard\WebAuthn\Server\Registration\RegistrationRequest;
use MadWizard\WebAuthn\Server\UserIdentity;
use MadWizard\WebAuthn\Server\WebAuthnServer;
use MadWizard\WebAuthnBundle\Manager\ContextStorageInterface;
use MadWizard\WebAuthnBundle\Manager\WebAuthnManager;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

class WebAuthnManagerTest extends TestCase
{
    /**
     * @var WebAuthnServer|PHPUnit_Framework_MockObject_MockObject
     */
    private $server;

    /**
     * @var ContextStorageInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $contextStorage;

    /**
     * @var WebAuthnManager
     */
    private $manager;

    protected function setUp()
    {
        $this->server = $this->createMock(WebAuthnServer::class);
        $this->contextStorage = $this->createMock(ContextStorageInterface::class);
        $this->manager = new WebAuthnManager($this->server, $this->contextStorage);
    }

    public function testStartRegistration()
    {
        $user = new UserIdentity(ByteBuffer::fromHex('1122'), 'dummy', 'Dummy user');


        $options = new RegistrationOptions($user);

        $regRequest = $this->createMock(RegistrationRequest::class);
        $regRequest->expects($this->any())
            ->method('getClientOptionsJson')
            ->willReturn(['dummy' => 'dummy']);
        $this->server->expects($this->once())->method('startRegistration')->willReturn($regRequest);
        $this->contextStorage->expects($this->once())->method('addContext')->willReturn('aabbccddee');

        $clientOptions = $this->manager->startRegistration($options);
        $this->assertSame(['dummy' => 'dummy'], $clientOptions->getRequestJson());
        $this->assertSame('create', $clientOptions->getType());
        $this->assertSame('aabbccddee', $clientOptions->getContextKey());

    }
}
