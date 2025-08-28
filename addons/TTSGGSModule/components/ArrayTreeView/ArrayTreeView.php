<?php

namespace ModulesGarden\TTSGGSModule\Components\ArrayTreeView;

use ModulesGarden\TTSGGSModule\Components\ArrayTreeView\Traits\ElementsExpanderTrait;
use ModulesGarden\TTSGGSModule\Components\ArrayTreeView\Traits\ElementsPrefixTrait;
use ModulesGarden\TTSGGSModule\Components\ArrayTreeView\Traits\ExpanderOnBeginningTrait;
use ModulesGarden\TTSGGSModule\Components\ArrayTreeView\Traits\KeyValueSeparatorTrait;
use ModulesGarden\TTSGGSModule\Components\ArrayTreeView\Traits\HiddenKeysModeTrait;
use ModulesGarden\TTSGGSModule\Components\ArrayTreeViewItem\ArrayTreeViewItem;
use ModulesGarden\TTSGGSModule\Core\Components\AbstractComponent;

class ArrayTreeView extends AbstractComponent
{
    use ElementsPrefixTrait;
    use KeyValueSeparatorTrait;
    use ElementsExpanderTrait;
    use ExpanderOnBeginningTrait;
    use HiddenKeysModeTrait;

    public const COMPONENT = 'ArrayTreeView';
    public const DEFAULT_ELEMENTS_EXPANDER = '(...)';

    protected array $elements = [];

    public function __construct(array $elements = [])
    {
        parent::__construct();

        $this->elements = $elements;
        $this->setElementsExpander(self::DEFAULT_ELEMENTS_EXPANDER);
    }

    final public function elementsSlotBuilder():array
    {
        return $this->buildElements();
    }

    protected function buildElements():array
    {
        $elements = [];

        foreach ($this->elements as $key => $value)
        {
            $elements[] = (new ArrayTreeViewItem($key, $value))
                ->setElementsPrefix($this->elementsPrefix)
                ->setKeyValueSeparator($this->keyValueSeparator)
                ->setElementsExpander($this->elementsExpander)
                ->enableExpanderOnBeginning($this->expanderOnBeginning)
                ->enableHiddenKeysMode($this->hiddenKeysMode);
        }

        return $elements;
    }
}