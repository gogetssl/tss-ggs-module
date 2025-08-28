<?php

namespace ModulesGarden\TTSGGSModule\Core\Components\Traits;

use Exception;

/**
 * Trait ElementsTrait
 */
trait ImageTrait
{
    /**
     * @param string $imagePath
     * @return self
     * @throws Exception
     */
    public function setImagePath(string $imagePath): self
    {
        if (!file_exists($imagePath))
        {
            throw new Exception(__CLASS__ . ': Image does not exists');
        }

        $contentType = mime_content_type($imagePath);
        $content     = base64_encode(file_get_contents($imagePath));

        $this->setSlot('img', 'data:' . $contentType . ';base64,' . $content);

        return $this;
    }

    /**
     * @param string $imageUrl
     * @return self
     */
    public function setImageUrl(?string $imageUrl): self
    {
        $this->setSlot('img', $imageUrl);

        return $this;
    }
}
