<?php

namespace ModulesGarden\TTSGGSModule\Core;

use ModulesGarden\TTSGGSModule\Core\Events\Dispatcher;
use ModulesGarden\TTSGGSModule\Core\Services\Translator;
use ModulesGarden\TTSGGSModule\Core\Services\Validator;

function make($class)
{
    return DependencyInjection::get($class);
}

/**
 * @param $event
 * @return mixed
 */
function fire($event, $payload = [])
{
    return DependencyInjection::call(Dispatcher::class)->fire($event, $payload);
}

/**
 * @return Dispatcher
 */
function dispatcher(): Dispatcher
{
    return DependencyInjection::call(Dispatcher::class);
}

/**
 * @return mixed
 */
function listen($event, $listener)
{
    return DependencyInjection::call(Dispatcher::class)->listen($event, $listener);
}

/**
 * @param $job
 * @param $arguments
 * @return mixed
 */
function queue(string $job, $arguments)
{
    return DependencyInjection::call(Dispatcher::class)->queue($job, $arguments);
}

/**
 * @return Translator::class
 */
function translator(): Translator
{
    return DependencyInjection::get(Translator::class);
}

/**
 * @param $lang
 * @return mixed
 */
function translate(string $lang)
{
    return DependencyInjection::get(Translator::class)->get($lang);
}

/**
 * @return Validator
 */
function validator(): Validator
{
    return DependencyInjection::get(Validator::class);
}
