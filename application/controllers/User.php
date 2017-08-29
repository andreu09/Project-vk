<?php

class User extends CI_controller
{

    private $params_authorization = [
        'client_id'     => 6161889,
        'redirect_uri'  => 'http://project-vk.ru/User/authorization',
        'display'       => 'popup',
        'response_type' => 'code',
        'v'             => 5.68
    ];

    public function index()
    {

        $this->twig->display('welcome',[
            'url_authorization' => 'http://oauth.vk.com/authorize?' . http_build_query($this->params_authorization),
            'name'  =>  'Вася'
        ]);
    }

    public function authorization()
    {
        if(!isset($this->session->uid)) {
           
            if(!empty($this->input->get('code'))) {

            } else {

                // Код не пришел
                show_error('Код от Вконтакте не был получен!',400,'Ошибка кода');
            }
            
        } else {
            // Уже авторизован
            redirect(base_url());
        }
    }
}