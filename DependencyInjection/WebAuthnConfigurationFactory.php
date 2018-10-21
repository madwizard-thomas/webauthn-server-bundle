<?php


namespace MadWizard\WebAuthnBundle\DependencyInjection;

use MadWizard\WebAuthn\Config\WebAuthnConfiguration;

class WebAuthnConfigurationFactory
{
    /**
     * @var array
     */
    private $settings;

    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }

    public function create() : WebAuthnConfiguration
    {
        $config = new WebAuthnConfiguration();

        $s = $this->settings;
        $config->setRelyingPartyOrigin($s['relying_party']['origin']);
        $config->setRelyingPartyName($s['relying_party']['name']);
        $config->setRelyingPartyId($s['relying_party']['id'] ?? null);
        $config->setRelyingPartyIconUrl($s['relying_party']['icon'] ?? null);

        if (($s['allowed_algorithms'] ?? null) !== null && count($s['allowed_algorithms']) > 0) {
            $config->setAllowedAlgorithms($s['allowed_algorithms']);
        }
        return $config;
    }
}
