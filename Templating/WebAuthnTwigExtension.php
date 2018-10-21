<?php


namespace MadWizard\WebAuthnBundle\Templating;

use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class WebAuthnTwigExtension extends AbstractExtension
{
    public function __construct()
    {
    }

    public function getFunctions()
    {
        return [
            new TwigFunction(
                'webauthn_form',
                [$this, 'twigWebAuthnForm'],
                [
                    'needs_environment' => true,
                    'is_safe' => ['html']
                ]
            )
        ];
    }

    public function twigWebAuthnForm(Environment $twig, ClientOptions $options) : string
    {
        return $twig->render('@MadWizardWebAuthn/form.html.twig', ['options' => $options]);
    }
}
