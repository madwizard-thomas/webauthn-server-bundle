WebAuthn Relying Party server Symfony Bundle
============================================

**Work in progress - use for testing purposes only**

Symfony bundle for [madwizard/webauthn](https://github.com/madwizard-thomas/webauthn-server) package. 

Resources
---------

[WebAuthn specification](https://www.w3.org/TR/webauthn/)

Installation
------------
Installation via composer:
```bash
composer require madwizard/webauthn-bundle:^0.0.1
```

Library reference
-----------------
Automatically built reference documentation (for both this library and the main WebAuthn library): \
https://madwizard-thomas.github.io/webauthn/

Configuration
-------------
**Note: preliminary information**\
This is a work in progress bundle - any part can change at any time until a stable version is released

1. Enable bundle in `config/bundles.php`

    ```php
    return [
        // ...
        MadWizard\WebAuthnBundle\MadWizardWebAuthnBundle::class => ['all' => true],
    ];
    ```

2. Configure bundle in file `config/packages/webauthn.yaml`
    ```yaml
    madwizard_webauthn:
        relying_party:
            # id: example.com
            name: My webauthn website
            origin: https://www.example.com 
            # Origin should match the host of your website and should be secure
            # (either encrypted via https or a trusted host like `localhost`).
            
        credential_store:
            service_id: App\Security\CredentialStore # See steps below 
         
    
        # challenge_length: 64
    ```

3. Write a class implementing `UserCredentialInterface`:
    ```php
    <?php
    
    use MadWizard\WebAuthn\Credential\UserCredentialInterface;
    use MadWizard\WebAuthn\Format\ByteBuffer;
    use MadWizard\WebAuthn\Crypto\CoseKey;
    
    class UserCred implements UserCredentialInterface
    {
        public function getCredentialId(): string
        {
            // Return credential id (base64url encoded string)
        }
    
        public function getPublicKey(): CoseKey
        {
            // Return public key
        }
    
        public function getUserHandle(): ByteBuffer
        {
            // Return user handle of credential's owner
        }
    }
    ```
    
4. Implement `CredentialStoreInterface` in a service specified in the configuration under `credential_store.service_id`.
     
    ```php
    <?php
    
    use MadWizard\WebAuthn\Credential\CredentialStoreInterface;
    use MadWizard\WebAuthn\Credential\UserCredentialInterface;
    use MadWizard\WebAuthn\Credential\CredentialRegistration;
    
    class CredentialStore implements CredentialStoreInterface
    {
        public function findCredential(string $credentialId): ?UserCredentialInterface
        {
            // find credential by credentialId. Return null if not found
        }
    
        public function registerCredential(CredentialRegistration $credential)
        {
            // Store credential represented by $credential
        }
           
        public function getSignatureCounter(string $credentialId): ?int
        {
            // Return signature counter
        }
    
        public function updateSignatureCounter(string $credentialId, int $counter): void
        {
            // Update signature counter for credential
        }  
    }
    ```
    
Usage
-----

### Registering a credential

```php
<?php

use MadWizard\WebAuthn\Exception\WebAuthnException;
use MadWizard\WebAuthn\Format\ByteBuffer;
use MadWizard\WebAuthn\Server\Registration\RegistrationOptions;
use MadWizard\WebAuthn\Server\UserIdentity;
use MadWizard\WebAuthnBundle\Manager\WebAuthnManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class RegistrationController extends Controller
{
    public function register(Request $request, WebAuthnManager $manager)
    {
        $vars = [];
        
        $posted = $request->isMethod('POST'); 
        $vars['posted'] = $posted; 
        try { 
            if (!$posted) {
                // Get user identity. Note that the userHandle should be a unique identifier for each user
                // (max 64 bytes). The WebAuthn specs recommend generating a random byte sequence for each
                // user. The code below is just for testing purposes! 
                $user = new UserIdentity(ByteBuffer::fromHex('aabbccdd'), 'dummy', 'Dummy user');
            
                // Setup options
                $options = new RegistrationOptions($user);

                // Get array with configuration for webauthn client                                
                $clientOptions = $manager->startRegistration($options);
                
                $vars['clientOptions'] = $clientOptions;
            } else {
                $result = $manager->finishRegistrationFromRequest($request);
                // Credential is now registered
                
                // For this demo, show credential ID via twig:
                $vars['credentialId'] = $result->getCredentialId();
            }
        } catch(WebAuthnException $e) {
            // NOTE: do not pass exception messages to the client. The exception messages could contain
            // security sensitive information useful for attackers.
            $vars['error'] = "Registration failed";
        }

        return $this->render('register.html.twig', $vars);
    }
}
```

The example code uses the webauthn-ui npm package to do the client side processing.\

Base template modification:
```twig
{# Add in <head> of base.html.twig #}

<script src="https://unpkg.com/webauthn-ui@0.0.2/dist/umd/webauthn-ui.min.js"></script>
```
Alternatively you can `npm install webauthn-ui` and use your own bundler like webpack to include the script.

register.html.twig:
```yaml
{% extends "base.html.twig" %}
{% block body %}

    Register credential:

    {% if error is defined %}

        <p>{{ error }}</p>

    {% elseif posted %}

        <p>Registered credential with id : <code>{{ credentialId }}</code></p>

    {% else %}

        <p>Please perfom the user action your authenticator.</p>
        {{ webauthn_form(clientOptions) }}

    {% endif %}
{% endblock %}

```

### Authenticating with credentials (second factor mode)

```php
<?php

use MadWizard\WebAuthn\Exception\WebAuthnException;
use MadWizard\WebAuthnBundle\Manager\WebAuthnManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use MadWizard\WebAuthn\Server\Authentication\AuthenticationOptions;

class AuthenticationController extends Controller
{
    public function authenticate(Request $request, WebAuthnManager $manager, CredentialStore $store)
    {
        $vars = [];
        
        $posted = $request->isMethod('POST');
        $vars['posted'] = $posted;
        try {
            if (!$posted) {
                $options = new AuthenticationOptions();
                
                // Specify which credentials are allowed to authenticate
                // Normally there can be multiple credentials for one user. This example just adds one.
                $credential = $store->findCredential('---DEMO CREDENTIAL ID HERE---');       // <!---- modify this
                $options->addAllowCredential($credential);
                
                // Get array with configuration for webauthn client
                $clientOptions = $manager->startAuthentication($options);
                $vars['clientOptions'] = $clientOptions;
            } else {
                $result = $manager->finishAuthenticationFromRequest($request);
                $vars['credentialId'] = $result->getUserCredential()->getCredentialId();
            }
        } catch(WebAuthnException $e) {
            // NOTE: do not pass exception messages to the client. The exception messages could contain
            // security sensitive information useful for attackers.
            $vars['error'] = "Authentication failed";
        }
        
        return $this->render('authentication.html.twig', $vars);
    }

}
````

authentication.html.twig:
```yaml
{% extends "base.html.twig" %}
{% block body %}

    Authenticate with credential:

    {% if error is defined %}

        <p>{{ error }}</p>

    {% elseif posted %}

        <p>Authenticated as user: <code>{{ credentialId }}</code></p>

    {% else %}

        <p>Please perfom the user action your authenticator.</p>
        {{ webauthn_form(clientOptions) }}

    {% endif %}
{% endblock %}
```
