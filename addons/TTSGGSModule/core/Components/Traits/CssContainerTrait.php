<?php

namespace ModulesGarden\TTSGGSModule\Core\Components\Traits;

/**
 * Trait ElementsTrait
 */
trait CssContainerTrait
{
    protected $css;

    /**
     * @return string
     */
    protected function cssSlotBuilder()
    {
        return $this->css;
    }

    /**
     * @param mixed $text
     * @return TextTrait
     */
    public function setCss($css): self
    {
        $this->css = $css;

        return $this;
    }

    public function addClass(string $css): self
    {
        $this->appendCss($css);

        return $this;
    }

    public function appendCss($css): self
    {
        $this->css .= ' ' . $css;

        return $this;
    }
}
