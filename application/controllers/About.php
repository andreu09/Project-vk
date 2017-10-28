<?php

class About extends CI_controller
{
    public function index() : void 
    {
        $this->twig->display('About',[

                'title'             => 'О сервисе'

        ]);
    }
}