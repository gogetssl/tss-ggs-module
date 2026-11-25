<?php

namespace TSSGGSModule\App\Libs\Settings;

/**
 * What a settings save says back: a message for the screen, as success or as an error (the save did not happen).
 * The message may hold simple HTML (<br>, the check marks of the API test).
 */
class Result
{
    protected $message = '';
    protected $error   = false;
    protected $redirect = '';

    public function setSuccess(string $message): self
    {
        $this->message = $message;
        $this->error   = false;

        return $this;
    }

    public function setError(string $message): self
    {
        $this->message = $message;
        $this->error   = true;

        return $this;
    }

    /** The page to open after the save (the setup wizard's next step) */
    public function setRedirect(string $url): self
    {
        $this->redirect = $url;

        return $this;
    }

    public function isError(): bool
    {
        return $this->error;
    }

    public function message(): string
    {
        return $this->message;
    }

    public function redirect(): string
    {
        return $this->redirect;
    }
}
