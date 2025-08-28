<?php

namespace ModulesGarden\TTSGGSModule\Core\UI\Messages;

class Messages
{
    protected array $messages = [];

    public function flash(string $message): Message
    {
        $message = (new Message())
            ->asFlash()
            ->setText($message)
            ->withSuccess();

        $this->add($message);

        return $message;
    }

    public function add(Message $message): self
    {
        $this->messages[] = $message;

        return $this;
    }

    public function toast(string $message): Message
    {
        $message = (new Message())
            ->asToast()
            ->setText($message)
            ->withSuccess();

        $this->add($message);

        return $message;
    }

    public function alert(string $message): Message
    {
        $message = (new Message())
            ->asAlert()
            ->setText($message)
            ->withDanger();

        $this->add($message);

        return $message;
    }

    protected function getByType(string $type): array
    {
        return array_filter($this->messages, function(Message $message) use ($type) {
            return $message->getDisplayType() == $type;
        });
    }

    public function getFlashes(): array
    {
        return $this->getByType(Message::TYPE_FLASH);
    }

    public function getAlerts(): array
    {
        return $this->getByType(Message::TYPE_ALERT);
    }

    public function getToastes(): array
    {
        return $this->getByType(Message::TYPE_TOAST);
    }
}