<?php

namespace ModulesGarden\TTSGGSModule\Core\Routing;

class Route
{
    protected string $callable = '';
    protected string $controller = '';
    protected string $action = '';
    protected string $name = '';

    public function __construct(string $callable)
    {
        $this->callable = $callable;
        [$this->controller, $this->action] = explode('@', $this->callable);
        $this->name = array_reverse(explode('\\', $this->controller))[0];
    }

    public function getCallable(): string
    {
        return $this->callable;
    }

    public function getController(): string
    {
        return $this->controller;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function is(string $name, ?string $action = ''): bool
    {
        return strcasecmp($this->getName(), $name) === 0 && (($action && strcasecmp($action, $this->getAction()) === 0) || !$action);
    }
}