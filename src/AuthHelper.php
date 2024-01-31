<?php

namespace Rybel\backbone;

abstract class AuthHelper {
    abstract function isLoggedIn();

    abstract function isAdmin();
}