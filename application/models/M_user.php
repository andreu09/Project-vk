<?php

class M_user extends CI_Model
{
    public function authorization($user,$user_friends) : bool
    {
        $result = false;

        if($this->exist_user($user->uid)) {

            // Существующий пользователь
            $this->db->update('users', $user, [
                'uid'   => $user->uid
            ]);

            // Получаем из базы друзей пользователя
            $user_friends_db =  $this->db->get_where('users_friends', [

                'uid'   => $user->uid

            ])->result();

            foreach($user_friends_db as $user_friend_db) {

                $user_friends_db_uids[] = $user_friend_db->uid_friend;

            }

            foreach($user_friends as $user_friend) {

                $user_friens_uids[] = $user_friend->uid;

            }

            // Удаленные друзья
            $remote_friends = array_diff($user_friends_db_uids, $user_friens_uids);
            // Новые друзья
            $new_friends = array_diff($user_friens_uids,$user_friends_db_uids);

            // Если изменений с базой нету
            if(empty($remote_friends) && empty($new_friends)) {
                
                $result = true;

                $this->db->update('users_friends', [

                    'exist'         => true

                ], [
                    
                    'uid'           => $user->uid,
                    'exist'         => false
                    
                ]);
            // Если есть новые друзья
            } elseif(empty($remote_friends) && !empty($new_friends)) {
                
                foreach($new_friends as $key => $new_friend) {
                    
                    $data = [
                        
                        'uid'           => $user->uid,
                        'uid_friend'    => $user_friends[$key]->uid,
                        'first_name'    => $user_friends[$key]->first_name,
                        'last_name'     => $user_friends[$key]->last_name,
                        'photo_50'      => $user_friends[$key]->photo_50,
                        'exist'         => true,
                        'date'          => unix_to_human(now("Europe/Moscow"),false,'euro')
                    ];
    
                    $this->db->insert('users_friends', $data);
                }
            // Если есть удаленные друзья
            } elseif(!empty($remote_friends) && empty($new_friends)) {

                foreach($remote_friends as $key => $remote_friend) {

                    $data = [
                        
                        'uid'           => $user->uid,
                        'uid_friend'    => $user_friends_db[$key]->uid_friend,
                        'first_name'    => $user_friends_db[$key]->first_name,
                        'last_name'     => $user_friends_db[$key]->last_name,
                        'photo_50'      => $user_friends_db[$key]->photo_50,
                        'exist'         => false,
                        'date'          => unix_to_human(now("Europe/Moscow"),false,'euro')
                    ];

                    $this->db->update('users_friends', $data, [

                        'uid'           => $user->uid,
                        'uid_friend'    => $remote_friend,
                        'exist'         => true

                    ]);

                    $result = true;

                }
            // Если есть новые и удаленные друзья
            } elseif(!empty($remote_friends) && !empty($new_friends)) {
                // Работаем с удаленными
                foreach($remote_friends as $key => $remote_friend) {

                    $data = [
                        
                        'uid'           => $user->uid,
                        'uid_friend'    => $user_friends_db[$key]->uid_friend,
                        'first_name'    => $user_friends_db[$key]->first_name,
                        'last_name'     => $user_friends_db[$key]->last_name,
                        'photo_50'      => $user_friends_db[$key]->photo_50,
                        'exist'         => false,
                        'date'          => unix_to_human(now("Europe/Moscow"),false,'euro')
                    ];

                    $this->db->update('users_friends', $data, [

                        'uid'           => $user->uid,
                        'uid_friend'    => $remote_friend,
                        'exist'         => true

                    ]);

                    $result = true;

                }
                // Работаем с новыми
                foreach($new_friends as $key => $new_friend) {
                    
                    $data = [
                        
                        'uid'           => $user->uid,
                        'uid_friend'    => $user_friends[$key]->uid,
                        'first_name'    => $user_friends[$key]->first_name,
                        'last_name'     => $user_friends[$key]->last_name,
                        'photo_50'      => $user_friends[$key]->photo_50,
                        'exist'         => true,
                        'date'          => unix_to_human(now("Europe/Moscow"),false,'euro')
                    ];
    
                    $this->db->insert('users_friends', $data);
                }

            }

        } else {

            // Новый пользователь
           $this->db->insert('users', $user);

            foreach($user_friends as $user_friend) {

                $data = [
                    
                    'uid'           => $user->uid,
                    'uid_friend'    => $user_friend->uid,
                    'first_name'    => $user_friend->first_name,
                    'last_name'     => $user_friend->last_name,
                    'photo_50'      => $user_friend->photo_50,
                    'exist'         => true,
                    'date'          => unix_to_human(now("Europe/Moscow"),false,'euro')
                ];

                $this->db->insert('users_friends', $data);
            }
            
            $result = true;
        }
    }

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
}