<?php

namespace Wexo\HelloRetail\Export;

class FeedEntity implements FeedEntityInterface
{
    private $file;
    private $associations;
    private $template;

    public function getFile()
    {
        return $this->file;
    }

    public function getAssociations()
    {
        return $this->associations;
    }

    public function getTemplate()
    {
        return $this->template;
    }

    public function setFile($file)
    {
        $this->file = $file;
    }

    public function setAssociations($associations)
    {
        $this->associations = $associations;
    }

    public function setTemplate($template)
    {
        $this->template = $template;
    }
}
