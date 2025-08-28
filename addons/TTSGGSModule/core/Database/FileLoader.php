<?php

namespace ModulesGarden\TTSGGSModule\Core\Database;

use Illuminate\Database\Capsule\Manager;
use ModulesGarden\TTSGGSModule\Core\FileReader\Reader;

/**
 * Autometes some of database queries
 *
 * @author
 */
class FileLoader
{
    /**
     * Helper to perform raw queries for module
     *
     * @param string $file
     * @return array
     */
    public function performQueryFromFile(string $file)
    {
        return $this->checkIsAllSuccess(array_map([$this, 'execute'], $this->getQueries($file)));
    }

    protected function checkIsAllSuccess(array $array = [])
    {
        return in_array(false, $array, true);
    }

    protected function getQueries($file)
    {
        return array_filter(explode(';', Reader::read($file)->get()), function($element) {
            $tElement = trim($element);
            if ($element === '' || $tElement === '')
            {
                return false;
            }

            return true;
        });
    }

    protected function execute($query)
    {
        $pdo = Manager::connection()->getPdo();
        if (empty($query) === false)
        {
            $statement = $pdo->prepare($query);
            $statement->execute();
        }
    }
}
