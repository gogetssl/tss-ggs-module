<?php

namespace ModulesGarden\TTSGGSModule\Core\Exporters;

use ModulesGarden\TTSGGSModule\Core\Exporters\Source\DataModelInterface;

class PhpExporter extends BaseExporter
{
    protected string $prefix;

    public function __construct(DataModelInterface $dataSet, string $prefix)
    {
        parent::__construct($dataSet);

        $this->prefix = $prefix;
    }

    public function get(): string
    {
        $content = "<?php" . PHP_EOL . PHP_EOL;

        $variableName = "$" . trim($this->prefix, "$");

        if ($this->dataSet instanceof \Traversable)
        {
            foreach ($this->dataSet as $key => $value)
            {
                $content .= $variableName . "['" . $key . "'] = " . $value . ";" . PHP_EOL;
            }

            return $content;
        }

        if ($this->dataSet instanceof \Stringable)
        {
            return $content . $variableName . ' = "' . trim($this->dataSet->__toString(), "'\"") . "\";" . PHP_EOL;
        }

        throw new \Exception("Wrong data type provided");
    }
}