<?php

namespace Rybel\backbone;

class page
{
    private $content;
    public $requiresAuth;

    public function __construct($loginNeeded = false)
    {
        $this->requiresAuth = $loginNeeded;
    }

    public function render()
    {
        echo $this->content;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }
}
