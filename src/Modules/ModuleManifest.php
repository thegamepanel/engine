<?php
declare(strict_types=1);

namespace Engine\Modules;

final readonly class ModuleManifest
{
    public string $ident;

    public string $name;

    public string $description;

    /**
     * @var array<\Engine\Modules\Capability>
     */
    public array $capabilities;

    public bool $unrestricted;

    /**
     * @param string                            $ident
     * @param string                            $name
     * @param string                            $description
     * @param array<\Engine\Modules\Capability> $capabilities
     */
    public function __construct(
        string $ident,
        string $name,
        string $description,
        array  $capabilities
    )
    {
        $this->ident        = $ident;
        $this->name         = $name;
        $this->description  = $description;
        $this->capabilities = $capabilities;
        $this->unrestricted = in_array(Capability::Unrestricted, $capabilities, true);
    }

    public function can(Capability $capability): bool
    {
        return $this->unrestricted || in_array($capability, $this->capabilities, true);
    }
}
