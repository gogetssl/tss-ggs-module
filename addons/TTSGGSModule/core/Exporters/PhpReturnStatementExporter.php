<?php

namespace ModulesGarden\TTSGGSModule\Core\Exporters;

class PhpReturnStatementExporter extends BaseExporter
{
    public function get(): string
    {
        $content = "<?php" . PHP_EOL . PHP_EOL;

        if ($this->dataSet instanceof \Traversable)
        {
            $content .= "return [ " . PHP_EOL;

            foreach ($this->dataSet as $key => $value)
            {
                $content .=  "\t'" . $key . "' => " . $value . "," . PHP_EOL;
            }

            $content .= "];";

            return $content;
        }

        if ($this->dataSet instanceof \Stringable)
        {
            $content .= "return \"" . trim($this->dataSet->__toString(), "'\"") . "\";" ;

            return $content;
        }

        throw new \Exception("Wrong data type provided");
    }
}