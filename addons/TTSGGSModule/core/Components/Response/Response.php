<?php

namespace ModulesGarden\TTSGGSModule\Core\Components\Response;

use ModulesGarden\TTSGGSModule\Core\Contracts\ResponseInterface;

/**
 *  Ajax Response Model
 */
class Response implements ResponseInterface
{
    protected const STATUS_ERROR   = 'error';
    protected const STATUS_SUCCESS = 'success';
    /**
     * @var array
     */
    protected array $actions = [];
    /**
     * @var array
     */
    protected array $data = [];
    /**
     * @var string|null
     */
    protected ?string $message = null;
    /**
     * @var string|null
     */
    protected ?string $status = self::STATUS_SUCCESS;

    /**
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }


    /**
     * Set data, usually it will be component content
     * @param array $data
     * @return $this
     */
    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Set action. For example reloadParent
     * @param array $actions
     * @return $this
     */
    public function setActions(array $actions): self
    {
        $this->actions = $actions;

        return $this;
    }

    /**
     * Set success message
     * @param string $message
     * @return $this
     */
    public function setSuccess(string $message): self
    {
        $this->message = $message;
        $this->status  = self::STATUS_SUCCESS;

        return $this;
    }

    /**
     * Set error message
     * @param string $message
     * @return $this
     */
    public function setError(string $message): self
    {
        $this->message = $message;
        $this->status  = self::STATUS_ERROR;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'status'  => $this->status,
            'message' => $this->message,
            'data'    => $this->data,
            'actions' => $this->actions,
        ];
    }
}
