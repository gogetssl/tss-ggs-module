<?php

namespace ModulesGarden\TSSGGSModule\Components\MigrationRunner;

use ModulesGarden\TSSGGSModule\Core\Components\AbstractComponent;

/**
 * Drives a running legacy migration from the browser: calls the batch endpoint until the run is finished
 * and shows the progress. Pure display component; all logic is in App\Migration\Engine.
 */
class MigrationRunner extends AbstractComponent
{
    public const COMPONENT = 'MigrationRunner';

    public function setRunId(int $runId): self
    {
        $this->setSlot('runId', $runId);

        return $this;
    }

    public function setBatchUrl(string $url): self
    {
        $this->setSlot('batchUrl', $url);

        return $this;
    }

    public function setToken(string $token): self
    {
        $this->setSlot('token', $token);

        return $this;
    }

    public function setInitial(array $progress): self
    {
        $this->setSlot('initial', $progress);

        return $this;
    }

    public function setLabels(array $labels): self
    {
        $this->setSlot('labels', $labels);

        return $this;
    }
}
