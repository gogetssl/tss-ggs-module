<?php

namespace ModulesGarden\TSSGGSModule\Core\UI\PageParams;

use ModulesGarden\TSSGGSModule\Core\UI\PageParams\ModuleActions\DefaultAction;
use ModulesGarden\TSSGGSModule\Core\UI\PageParams\Source\ModuleActionInterface;

class ModuleActionsFactory
{
    const ACTIONS_DIR = 'ModuleActions';

    public static function getFromParams(array $params = []): ModuleActionInterface
    {
        if (empty($params['action']))
        {
            return new DefaultAction();
        }

        $actionName = ucfirst($params['action']);

        $className = __NAMESPACE__ . '\\' . self::ACTIONS_DIR . '\\' . $actionName;

        if (!class_exists($className) )
        {
            return new DefaultAction();
        }

        $actionObject = new $className();

        if (!is_subclass_of($actionObject,ModuleActionInterface::Class ))
        {
            return new DefaultAction();
        }

        return $actionObject;
    }
}