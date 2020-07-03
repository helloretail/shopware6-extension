<?php

namespace Wexo\HelloRetail\Export;

interface FeedEntityInterface
{
    public function getFile();

    public function getAssociations();

    public function getTemplate();

    public function setFile($file);

    public function setAssociations($associations);

    public function setTemplate($template);
}
