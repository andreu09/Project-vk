<?php

class User extends CI_controller
{
    private $params_authorization = [

        'client_id'     => 6161889,
        'redirect_uri'  => 'http://project-vk.ru/User/authorization',
        'display'       => 'popup',
        'scope'         => 'friends,offline,video',
        'response_type' => 'code',
        'v'             => 5.68,
    ];

    public function index() : void
    {

        if( isset($this->session->uid) ) {

            $this->twig->display('user/statistics',[

                'title'                  => 'Статистика',
                'user'                   => $this->M_user->get($this->session->uid),
                'user_photos'            => $this->M_vk_api->get_photos($this->session->uid),
                'user_videos'            => $this->M_vk_api->get_videos($this->session->uid),
                'user_friends'           => $this->M_vk_api->get_friends($this->session->uid),   

            ]);

        } else {

             $this->twig->display('user/authorization',[

                'url_authorization' => 'http://oauth.vk.com/authorize?' . urldecode(http_build_query($this->params_authorization)),
                'title'             => 'Подтверждение входа'

            ]);

        }

    }

    public function authorization() : void
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

                $this->M_user->add_token($vk->access_token,$vk->user_id);
                
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

                    redirect(base_url());

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

    public function out() : void 
    {
        if( isset($this->session->uid) ) {

            $this->session->unset_userdata('uid');
            redirect(base_url());

        } else {

            redirect(base_url());
        }
    }

    public function faq() : void 
    {
        if( isset($this->session->uid) ) {

            $this->twig->display('user/faq',[

                'user_info' => $this->M_user->user_get_info($this->session->uid),
                'title'    => 'Часто задаваемые вопросы'

            ]);
            
        } else {

            redirect(base_url());

        }
    }

}