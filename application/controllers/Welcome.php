<?php

class Welcome extends CI_controller 
{
    public function index()
    {
        $data = [
            "name"  => "Вася"
        ];
        
        $this->twig->display('welcome', $data);
    }
}