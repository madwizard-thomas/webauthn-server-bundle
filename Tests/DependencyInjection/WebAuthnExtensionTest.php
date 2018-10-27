<?php


namespace MadWizard\WebAuthnBundle\Tests\DependencyInjection;

use MadWizard\WebAuthnBundle\DependencyInjection\WebAuthnExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Parser;

class WebAuthnExtensionTest extends TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @var WebAuthnExtension
     */
    private $extension;

    protected function setUp()
    {
        $this->container = new ContainerBuilder();
        $this->extension = new WebAuthnExtension();
    }

    public function testMinimalConfig()
    {
        $this->extension->load([$this->getTestConfig()], $this->container);

        $configFactoryArg = [
            'relying_party' =>
                [
                    'origin' => 'https://example.com',
                    'name' => 'The relying party',
                ],
            'credential_store' =>
                [
                    'service_id' => 'credential_store_service',
                ],
            'allowed_algorithms' =>
                [],
        ];
        $this->assertSame(
            $configFactoryArg,
            $this->container->getDefinition('madwizard_webauthn.config_factory')->getArgument(0)
        );
    }

    public function getTestConfig()
    {
        $config = <<<'EOF'

relying_party:
    origin: https://example.com
    name: 'The relying party'
credential_store:
    service_id: credential_store_service
EOF;

        return (new Parser())->parse($config);
    }
}
