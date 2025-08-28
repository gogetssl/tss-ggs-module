<?php

namespace ModulesGarden\TTSGGSModule\Core\Http;

use ModulesGarden\TTSGGSModule\Core\Helper\BuildUrl;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;

/**
 * @method  __construct(string $url, int $status = 302, array $headers = [])
 */
class RedirectResponse extends SymfonyRedirectResponse
{
    protected $lang;

    /**
     * @inheritdoc
     * @TODO - te matoda powina sie nazywac create i nadpsiywac istniejaca juz
     */
    public static function createByUrl($url = '', array $params = [])
    {
        return new static($url . ((count($params) . 0) ? ('?' . http_build_query($params)) : ''));
    }

    /**
     * @inheritdoc
     * @TODO - te matoda powina sie nazywac create i nadpsiywac istniejaca juz
     */
    public static function createMG($controller = null, $action = null, array $params = [])
    {
        return new static(BuildUrl::getUrl($controller, $action, $params));
    }

    public function getLang()
    {
        return $this->lang;
    }

    public function setLang($lang)
    {
        $this->lang = $lang;

        return $this;
    }
}
