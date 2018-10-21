<?php


namespace MadWizard\WebAuthnBundle\Exception;

class ClientRegistrationException extends RegistrationException
{
    private $type;

    public function __construct(string $message, ?string $type)
    {
        parent::__construct(sprintf('Client javascript error: %s: %s.', $type, $message));
        $this->type = $type;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }
}
