<?php

namespace ModulesGarden\TSSGGSModule\App\Libs\Settings;

use ModulesGarden\TSSGGSModule\App\Kernel\Lang;

/**
 * Base of a settings screen's data: read() fills $data (the values the screen shows) and $availableValues (the
 * choices of its dropdowns), update() saves $formData (what was posted) and may return a Result - an error stops
 * the save and is shown on the screen.
 *
 *   $provider = new GeneralSettings();          $provider = new GeneralSettings($posted);
 *   $provider->read();                          $result   = $provider->update();
 *   $provider->values(); $provider->options();
 *
 * The screens' texts keep the keys they always had: TEXTS is the group of the provider's own texts
 * ("admin.settings.index.providers.general_settings_provider").
 */
abstract class Provider
{
    const TEXTS = '';

    /** @var Bag what was posted */
    protected $formData;
    /** @var Bag the values of the screen */
    protected $data;
    /** @var Bag field name => [value => label] */
    protected $availableValues;
    /** @var Lang */
    protected $lang;

    public function __construct(array $formData = [], Lang $lang = null)
    {
        $this->formData        = new Bag($formData);
        $this->data            = new Bag();
        $this->availableValues = new Bag();
        $this->lang            = $lang ?: new Lang('admin');
    }

    abstract public function read();

    abstract public function update();

    public function values(): array
    {
        return $this->data->toArray();
    }

    public function options(): array
    {
        return $this->availableValues->toArray();
    }

    /**
     * A text of the provider's own group, or of another group ($groups, the first that has the key).
     * $replace: name => value for ":name" in the text (a leading ":" in the name is accepted).
     */
    public function translate(string $key, array $replace = [], array $groups = []): string
    {
        $names = [];
        foreach($replace as $name => $value)
        {
            $names[ltrim((string)$name, ':')] = $value;
        }
        foreach(array_merge($groups, [static::TEXTS]) as $group)
        {
            if($this->lang->has($group . '.' . $key))
            {
                return $this->lang->get($group . '.' . $key, $names);
            }
        }

        return $this->lang->get($key, $names);
    }
}
