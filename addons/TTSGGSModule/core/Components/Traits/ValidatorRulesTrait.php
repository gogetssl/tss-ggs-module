<?php

namespace ModulesGarden\TTSGGSModule\Core\Components\Traits;

trait ValidatorRulesTrait
{
    use ValidatorTrait;

    public function between($min, $max): self
    {
        $this->addValidator('between:' . $min . ',' . $max);

        return $this;
    }

    public function email(): self
    {
        $this->addValidator('email');

        return $this;
    }

    public function greaterThen($value): self
    {
        $this->addValidator('min:' . $value);

        return $this;
    }

    public function ipv4(): self
    {
        $this->addValidator('ipv4');

        return $this;
    }

    public function ipv6(): self
    {
        $this->addValidator('ipv6');

        return $this;
    }

    public function lowerThen($value): self
    {
        $this->addValidator('max:' . $value);

        return $this;
    }

    public function numeric(): self
    {
        $this->addValidator('numeric');

        return $this;
    }

    public function regex($pattern): self
    {
        $this->addValidator('regex:' . $pattern);

        return $this;
    }

    public function required(): self
    {
        $this->addValidator('required');

        return $this;
    }
}
