<?php

class User extends CI_controller
{

    private $params_authorization = [

        'client_id'     => 6161889,
        'redirect_uri'  => 'http://project-vk.ru/User/authorization',
        'display'       => 'popup',
        'scope'         => 'friends',
        'response_type' => 'code',
        'v'             => 5.68
    ];

    public function index()
    {

        $this->twig->display('welcome',[

            'url_authorization'     => 'http://oauth.vk.com/authorize?' . http_build_query($this->params_authorization),
            'user_friends_deleted'  => $this->M_user->get_friends($this->session->uid,'deleted',false),
            'user_friends_existing' => $this->M_user->get_friends($this->session->uid,'existing',false),
            'user_friends_all'      => $this->M_user->get_friends($this->session->uid,'all',true)

        ]);

    }

    public function authorization()
    {
        if(!isset($this->session->uid)) {
           
            if(!empty($this->input->get('code'))) {

                $params_get_token = [

                    'client_id'     => $this->params_authorization['client_id'],
                    'client_secret' =>  'ogdBf66XvS1gBgK8oUb6',
                    'redirect_uri'  => $this->params_authorization['redirect_uri'],
                    'code'          => $this->input->get('code')

                ];

                @$vk = json_decode(file_get_contents('https://oauth.vk.com/access_token?' . http_build_query($params_get_token)));
                
                if($vk) {

                    $params_user_get = [
                        
                        'user_id'   => $vk->user_id,
                        'fields'    => 'first_name,last_name,photo_50,photo_100'
                    ];

                   $user = json_decode(file_get_contents('https://api.vk.com/method/users.get?' . urldecode(http_build_query($params_user_get))))->response[0];

                   $params_user_get_friends = [

                        'user_ids'      => $vk->user_id,
                        'order'         => 'random',
                        'fields'        => 'photo_50',
                        'access_token'  => $vk->access_token
                    ];

                   $user_friends = json_decode(file_get_contents('https://api.vk.com/method/friends.get?' . urldecode(http_build_query($params_user_get_friends))))->response;

                  if( $this->M_user->authorization($user,$user_friends)) {

                    $this->session->set_userdata([

                        'uid'   => $vk->user_id

                    ]);

                    redirect(base_url() . 'user');

                  } else {

                    // Ошибка авторизации
                    show_error('Произошла ошибка при авторизации...',400,'Ошибка входа');

                  }

                } else {

                    // Токен устарел
                    show_error('Токен устарел!',400,'Ошибка токена');
                }

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