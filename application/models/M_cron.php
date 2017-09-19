<?php

class M_cron extends CI_Model
{
    private $access_service_key = 'a2fe1789a2fe1789a2fe17893ba2a01268aa2fea2fe1789fb5e75dade41b9e56ab7b22b';

    public function update_user_friends() : void
    {
       $users = $this->db->get('users')->result();

       // Собираем uid друзей пользователя из базы
       foreach($users as $user) {

            $users_uids[] =  $user->uid;

            $this->db->select('uid_friend,uid');
            $users_friends_uids_db_exist[$user->uid] =  $this->db->get_where('users_friends', [

                'uid'   => $user->uid,
                'exist' => true

            ])->result_array();

             $users_friends_uids_db_no_exist[$user->uid] =  $this->db->get_where('users_friends', [

                'uid'   => $user->uid,
                'exist' => false

            ])->result_array();
        }

        // Преобразовываем uids пользователей к виду uid пользователя => его друг
       foreach($users as $user) {

            // Существующие в базе
            foreach($users_friends_uids_db_exist[$user->uid] as $users_friends_uid_db_exist) {

                if($users_friends_uid_db_exist['uid'] == $user->uid ) {

                    $db_users_friends_exist_uids[$user->uid][] = $users_friends_uid_db_exist['uid_friend'];

                }

            }
            // Удаленные друзья в базе
            foreach($users_friends_uids_db_no_exist[$user->uid] as $users_friends_uid_db_no_exist) {

                if($users_friends_uid_db_no_exist['uid'] == $user->uid ) {

                    $db_users_friends_no_exist_uids[$user->uid][] = $users_friends_uid_db_no_exist['uid_friend'];

                }

            }

       }

       // Получаем друзей пользователей из вк
       foreach($users_uids as $users_uid) {

        $params_user_get_friends = [
            
                'user_id'      =>  (int) $users_uid,
                'order'         => 'random',
                'client_secret' => $this->access_service_key
            ];
    
           $vk_users_friends_uids[$users_uid] = json_decode(file_get_contents('https://api.vk.com/method/friends.get?' . urldecode(http_build_query($params_user_get_friends))))->response;
       }

        foreach($users as $user) {

            // Те ксто есть в базе но нету уже в вк, удаленные друзья
            $remote_user_uids_friends[$user->uid] = @array_diff($db_users_friends_exist_uids[$user->uid],$vk_users_friends_uids[$user->uid]);
            // Новые друзья
            $new_user_uids_friends[$user->uid] = @array_diff($vk_users_friends_uids[$user->uid],$db_users_friends_exist_uids[$user->uid]);
            // Старые друзья, которые в базе отмечены как удаленные, но вк вернул их, значит они теперь снова актианые
            $old_user_uids_friends[$user->uid] =  @array_intersect($vk_users_friends_uids[$user->uid],$db_users_friends_no_exist_uids[$user->uid]);

        }

        // Сортируем массивы чтобы ключи начинались с 0
        foreach($users as $user) {

            @sort($remote_user_uids_friends[$user->uid]);
            @sort($new_user_uids_friends[$user->uid]);
            @sort($old_user_uids_friends[$user->uid]);
            
        }

        foreach($users as $user) {

           if(!empty($remote_user_uids_friends[$user->uid])) {
                // Значит есть удаленные друзья
                foreach($remote_user_uids_friends[$user->uid] as $remote_user_uid_friends) {
                
                    $data = [

                        'exist' => false

                    ];

                    $where = [

                        'uid'           => $user->uid,
                        'uid_friend'    => $remote_user_uid_friends

                    ];

                    $this->db->update('users_friends', $data, $where);

                }

           }

            if(!empty($old_user_uids_friends[$user->uid])) {
                // Значит есть друзья которые помечены в базе как удаленные, но пользователь снова добавил этих друзей
                foreach($old_user_uids_friends[$user->uid] as $old_user_uid_friends) {
                
                    $data = [

                        'exist' => true

                    ];

                    $where = [

                        'uid'           => $user->uid,
                        'uid_friend'    => $old_user_uid_friends

                    ];

                    $this->db->update('users_friends', $data, $where);

                }

            }

            if(!empty($new_user_uids_friends[$user->uid])) {
                // Значит у пользователя появились новые друзья, которых нет в базе
                
                $param_get_info_user_friends = [

                    'user_ids'      =>  implode(',',$new_user_uids_friends[$user->uid]),
                    'order'         => 'random',
                    'fields'        => 'photo_50',
                    'client_secret' => $this->access_service_key

                ];

                $vk_user_new_friends =  json_decode(file_get_contents('https://api.vk.com/method/users.get?' . urldecode(http_build_query($param_get_info_user_friends))))->response;

                foreach( $vk_user_new_friends as $vk_user_new_friend) {

                    $data_new_user_friends = [
                        
                        'uid'           => $user->uid,
                        'uid_friend'    => $vk_user_new_friend->uid,
                        'first_name'    => $vk_user_new_friend->first_name,
                        'last_name'     => $vk_user_new_friend->last_name,
                        'photo_50'      => $vk_user_new_friend->photo_50,
                        'exist'         => true,
                        'date'          => unix_to_human(now("Europe/Moscow"),false,'euro')

                    ];

                    $this->db->insert('users_friends', $data_new_user_friends);

                }

            }

        }

    }
}