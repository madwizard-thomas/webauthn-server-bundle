<?php


namespace MadWizard\WebAuthnBundle\Manager;

use MadWizard\WebAuthn\Exception\WebAuthnException;
use MadWizard\WebAuthn\Json\JsonConverter;
use MadWizard\WebAuthn\Server\Authentication\AssertionContext;
use MadWizard\WebAuthn\Server\Authentication\AuthenticationOptions;
use MadWizard\WebAuthn\Server\Authentication\AuthenticationResult;
use MadWizard\WebAuthn\Server\Registration\AttestationContext;
use MadWizard\WebAuthn\Server\Registration\AttestationResult;
use MadWizard\WebAuthn\Server\Registration\RegistrationOptions;
use MadWizard\WebAuthn\Server\WebAuthnServer;
use MadWizard\WebAuthnBundle\Exception\ClientRegistrationException;
use MadWizard\WebAuthnBundle\Exception\RegistrationException;
use MadWizard\WebAuthnBundle\Templating\ClientOptions;
use Symfony\Component\HttpFoundation\Request;

class WebAuthnManager
{
    /**
     * @var WebAuthnServer
     */
    private $server;

    /**
     * @var ContextStorageInterface
     */
    private $contextStorage;

    public function __construct(WebAuthnServer $server, ContextStorageInterface $contextStorage)
    {
        $this->server = $server;
        $this->contextStorage = $contextStorage;
    }

    public function startRegistration(RegistrationOptions $options) : ClientOptions
    {
        $registrationRequest = $this->server->startRegistration($options);
        $key = $this->contextStorage->addContext($registrationRequest->getContext());
        return new ClientOptions('create', $registrationRequest->getClientOptionsJson(), $key);
    }

    public function finishRegistrationFromRequest(Request $request) : AttestationResult
    {
        $data = $this->extractRequestData($request);
        return $this->finishRegistration($data['response'], $data['contextKey']);
    }

    public function finishRegistration(string $responseJson, string $contextKey) : AttestationResult
    {
        $this->handleJsonErrorResponse($responseJson);

        $context = $this->contextStorage->getContext($contextKey);

        if ($context === null || !($context instanceof AttestationContext)) {
            throw new RegistrationException('Context key does not belong to appropriate context.');
        }

        try {
            $credential = JsonConverter::decodeAttestationCredential($responseJson);
            $attestationResult = $this->server->finishRegistration($credential, $context);
            $this->contextStorage->removeContext($contextKey);
            return $attestationResult;
        } catch (WebAuthnException $e) {
            throw new RegistrationException('Registration failed: ' . $e->getMessage(), 0, $e);
        }
    }

    public function startAuthentication(AuthenticationOptions $options) : ClientOptions
    {
        $authenticationRequest = $this->server->startAuthentication($options);
        $key = $this->contextStorage->addContext($authenticationRequest->getContext());
        return new ClientOptions('get', $authenticationRequest->getClientOptionsJson(), $key);
    }

    public function finishAuthenticationFromRequest(Request $request) : AuthenticationResult
    {
        $data = $this->extractRequestData($request);
        return $this->finishAuthentication($data['response'], $data['contextKey']);
    }

    public function finishAuthentication(string $responseJson, string $contextKey) : AuthenticationResult
    {
        $this->handleJsonErrorResponse($responseJson);

        $context = $this->contextStorage->getContext($contextKey);

        if ($context === null || !($context instanceof AssertionContext)) {
            throw new RegistrationException('Context key does not belong to appropriate context.');
        }

        try {
            $credential = JsonConverter::decodeAssertionCredential($responseJson);
            $authenticationResult = $this->server->finishAuthentication($credential, $context);
            $this->contextStorage->removeContext($contextKey);
            return $authenticationResult;
        } catch (WebAuthnException $e) {
            throw new RegistrationException('Registration failed: ' . $e->getMessage(), 0, $e);
        }
    }

    private function handleJsonErrorResponse(string $responseJson)
    {
        $map = \json_decode($responseJson, true, 10);
        if (!is_array($map)) {
            throw new RegistrationException('Invalid WebAuthn JSON response in POST data.');
        }

        if (($map['status'] ?? null) === 'failed') {
            $msg = (string) ($map['errorMessage'] ?? 'Unknown error');
            $type = (string) ($map['errorName'] ?? '');
            if ($type === '') {
                $type = null;
            }

            throw new ClientRegistrationException($msg, $type);
        }
    }

    private function extractRequestData(Request $request) : array
    {
        if (!$request->isMethod('POST')) {
            throw new RegistrationException('Request should be using POST method.');
        }

        $contextKey = $request->request->get('_webauthn_context_key', null);
        if (!is_string($contextKey)) {
            throw new RegistrationException('Missing context key field in POST data.');
        }

        $response = $request->request->get('_webauthn_response', null);
        if (!is_string($response)) {
            throw new RegistrationException('Missing WebAuthn response in POST data.');
        }
        return ['contextKey' => $contextKey, 'response' => $response];
    }
}
