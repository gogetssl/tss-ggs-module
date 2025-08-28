<?php

namespace ModulesGarden\TTSGGSModule\Core\Contracts;

/**
 * Interface DataSetInterface
 */
interface DataSetInterface
{
    public function getFullLength(): int;

    public function getLength(): int;

    public function getOffset(): int;

    public function getRecords();

    public function setFieldModifier(string $fieldName, callable $callback);
}
