<?php

namespace Rybel\backbone;

class page
{
    private $content;
    public $requiresAuth;

    /**
     * @param bool $loginNeeded
     */
    public function __construct(bool $loginNeeded = false)
    {
        $this->requiresAuth = $loginNeeded;
    }

    /**
     * @return void
     */
    public function render()
    {
        echo $this->content;
    }

    /**
     * @param $content
     * @return void
     */
    public function setContent($content)
    {
        $this->content = $content;
    }
}
