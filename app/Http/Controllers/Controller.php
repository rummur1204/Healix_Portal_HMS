<?php

namespace App\Http\Controllers;

abstract class Controller
{
    // This method is inherited by all controllers
    protected function middleware($middleware)
    {
        // This is a placeholder - the actual middleware() method
        // comes from Laravel's base controller trait
    }
}