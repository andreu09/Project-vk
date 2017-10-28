<?php

class M_user extends CI_Model
{

    // Авторизация

    public function authorization($user) : bool
    {
        $result = false;

        if($this->exist_user($user->uid)) {
            
            // Ненужная информация от вк
            unset($user->hidden);
            // Существующий пользователь
            $this->db->update('users', $user, [
                'uid'   => $user->uid
            ]);

            $result = true;

        } else {

            // Ненужная инфа
            unset($user->hidden);
            // Новый пользователь
            $this->db->insert('users', $user);

            $result = true;
        }

        return $result;
    }

    // Проверка пользователя на существование

    public function exist_user(int $uid) : bool
    {
        $result = false;

        $user =  $this->db->get_where('users', [

            'uid'   => $uid
        ])->row();

        if($user !== null) {
           
            $result = true;
        }

        return $result;
    }

    // Информация о юзере

    public function get(int $uid) 
    {
        $user = $this->db->get_where('users',[

            'uid'   => $uid

        ])->row();

        return $user;
    }

    public function add_token($token,$uid) 
    {
        $this->db->update('users',[ 'token' => $token ], [ 'uid' => $uid ]);
    }

}