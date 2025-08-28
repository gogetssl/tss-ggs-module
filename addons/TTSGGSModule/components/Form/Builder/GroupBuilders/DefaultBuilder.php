<?php

namespace ModulesGarden\TTSGGSModule\Components\Form\Builder\GroupBuilders;

use ModulesGarden\TTSGGSModule\Components\FormGroup\FormGroup;
use ModulesGarden\TTSGGSModule\Core\Components\FormFields\FormField;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\FormFieldInterface;
use function ModulesGarden\TTSGGSModule\Core\translator;

class DefaultBuilder
{
    protected $form;
    protected $defaultFormGroup;

    protected $builder;

    public function __construct($builder, $form, $defaultFormGroup)
    {
        $this->builder          = $builder;
        $this->form             = $form;
        $this->defaultFormGroup = $defaultFormGroup;
    }

    public function build(FormFieldInterface $field, bool $showTooltip = true, FormGroup $formGroup = null)
    {
        $name        = (new \ReflectionClass($field))->getShortName();
        $factoryName = $this->findBuilder($name);

        $title = (($field instanceof FormField) && !empty($field->getTitle()) ?
            $field->getTitle() :
            translator()->getBasedOnNamespaces([get_class($this->form)], $field->getName()));

        $factory = new $factoryName($this->builder);
        $factory->withFormGroup($formGroup ?: clone $this->defaultFormGroup);
        $factory->setTitle($title);

        if ($showTooltip)
        {
            $description = (($field instanceof FormField) && !empty($field->getDescription()) ?
                $field->getDescription() :
                translator()->getBasedOnNamespaces([get_class($this->form)], $field->getName() . '_description'));

            $factory->setDescription($description);
        }

        return $factory->create($field, $field->getName());
    }

    protected function findBuilder($name): string
    {
        $pieces    = array_filter(preg_split('/(?=[A-Z])/', $name));
        $namespace = '\\' . implode('\\', array_slice(explode('\\', __NAMESPACE__), 0, -1));

        while ($pieces)
        {
            $fieldFactoryName = $namespace . '\\Builders\\' . implode($pieces) . 'Factory';

            if (class_exists($fieldFactoryName))
            {
                return $fieldFactoryName;
            }

            array_pop($pieces);
        }

        return $namespace . '\\Builders\DefaultFactory';
    }
}