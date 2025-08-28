<?php

namespace ModulesGarden\TTSGGSModule\Packages\Product\Services;

use ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Server as Model;

class ServerConfiguration
{
    protected int $serverId;

    public function __construct(int $serverId)
    {
        $this->serverId = $serverId;
    }

    /**
     * @return ?array
     */
    public function get(): ?array
    {
        $server = Model::find($this->serverId);
        if (!$server) {
            return null;
        }

        return json_decode(html_entity_decode($server->accesshash), true) ?: [];
    }

    /**
     * @param $entries
     * @return $this
     */
    public function save(array $entries): self
    {
         Model::where('id', $this->serverId)->update(['accesshash' => json_encode($entries)]);
         return $this;
    }
}
