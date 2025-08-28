<?php

namespace ModulesGarden\TTSGGSModule\Core\Components\Traits;

use ModulesGarden\TTSGGSModule\Core\Components\DataBuilder;
use ModulesGarden\TTSGGSModule\Core\Components\Response\Response;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxAutoReloadInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxAutoSubmitInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxOnActionInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxOnLoadInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\ResponseInterface;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Request;

/**
 * Class AjaxComponent
 */
trait AjaxTrait
{
    /**
     * @var int defined in milliseconds
     */
    protected int $ajaxAutoReloadInterval = 30000;
    private ?bool $ajaxAutoReload = null;
    private ?bool $ajaxOnChange = null;
    private ?bool $ajaxOnLoad = null;
    private ?bool $ajaxAutoSubmit = null;

    protected ?array $ajaxStoreNames = null;

    protected function ajaxStoreNamesSlotBuilder(): ?array
    {
        return $this->ajaxStoreNames;
    }


    protected function ajaxAutoReloadIntervalSlotBuilder(): ?int
    {
        return ($this instanceof AjaxAutoReloadInterface || $this->ajaxAutoReload) && $this->ajaxAutoReloadInterval > 0 ? $this->ajaxAutoReloadInterval : null;
    }

    protected function ajaxOnActionSlotBuilder(): ?bool
    {
        return $this instanceof AjaxOnActionInterface ? true : $this->ajaxOnChange;
    }

    protected function ajaxOnLoadSlotBuilder(): ?bool
    {
        return $this instanceof AjaxOnLoadInterface ? true : $this->ajaxOnLoad;
    }

    protected function ajaxAutoSubmitSlotBuilder(): ?bool
    {
        return $this instanceof AjaxAutoSubmitInterface ? true : $this->ajaxAutoSubmit;
    }

    /**
     * Define ajaxData that will be returned to component. This data will be used in next Ajax query
     * @param array $data
     * @return $this
     */
    public function setAjaxData(array $data)/*: self*/
    {
        $this->setSlot('ajaxData', $data);

        return $this;
    }

    protected function propagateAjaxData(): void
    {
        $this->setAjaxData((array)array_merge((array)Request::get('ajaxData'), $this->getSlot('ajaxData', [])));
    }

    public function returnAjaxData(): ResponseInterface
    {
        try
        {
            //set default ajax data
            $this->propagateAjaxData();

            return new Response(
                (new DataBuilder($this))
                    ->withHtml()
                    ->withData()
                    ->toArray()
            );
        }
        catch (\Exception $ex)
        {
            return (new Response())
                ->setError($ex->getMessage());
        }
    }
}
