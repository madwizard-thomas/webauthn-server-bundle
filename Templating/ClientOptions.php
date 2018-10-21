<?php


namespace MadWizard\WebAuthnBundle\Templating;

class ClientOptions
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var array
     */
    private $requestJson;

    /**
     * @var string
     */
    private $contextKey;

    public function __construct(string $type, array $requestJson, string $contextKey)
    {
        $this->type = $type;
        $this->requestJson = $requestJson;
        $this->contextKey = $contextKey;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return array
     */
    public function getRequestJson(): array
    {
        return $this->requestJson;
    }

    /**
     * @return string
     */
    public function getContextKey(): string
    {
        return $this->contextKey;
    }
}
