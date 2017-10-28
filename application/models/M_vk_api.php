<?php

class M_vk_api extends CI_Model
{
    private $client_secret = 'a2fe1789a2fe1789a2fe17893ba2a01268aa2fea2fe1789fb5e75dade41b9e56ab7b22b';
    
        public function get_wall(int $id)  {
    
           $wall = $this->query('wall.get',"owner_id={$id}");
    
           return [
    
                'wall'  => [
    
                    'count' => $wall->response->count
                ]
           ];
        }

        public function get_photos(int $id)  {
    
           $photos = $this->query('photos.get',"owner_id={$id}&album_id=profile&extended=1");
           $count_all_likes = 0;
           $achievements = [];
           
           foreach($photos->response->items as $photo) {
               $count_all_likes += $photo->likes->count;
           }

           if($photo->likes->count > 100 && $photo->likes->count < 500) {
               $achievements['name'] = 'Лайки да и только';
               $achievements['make'] = 'Наберите более 100 лайков в профиле';

            } elseif($photo->likes->count > 500 && $photo->likes->count < 1000) {
                $achievements['name'] = 'Популярный';
                $achievements['make'] = 'Наберите более 500 лайков в профиле';

            } elseif($photo->likes->count > 1000 && $photo->likes->count < 5000) {
                $achievements['name'] = 'Звезда';
                $achievements['make'] = 'Наберите более 1000 лайков в профиле';

            } elseif($photo->likes->count > 5000) {
                $achievements['name'] = 'Самая(ый) известный!';
                $achievements['make'] = 'Наберите более 5000 лайков в профиле';
            }

           return [
    
                'photos'  => [
    
                   'count'      => $photos->response->count,
                   'count_all_likes'  => $count_all_likes,
                   'achievements' => $achievements
                ]
           ];
        }

        public function get_videos(int $id) {

            $videos = $this->query('video.get',"owner_id={$id}");
    
           return [
    
                'videos'  => [
    
                    'count' => $videos->response->count
                ]
           ];
        }
    
        public function get_friends(int $id) {
    
            $friends =  $this->query('friends.get',"user_id={$id}");
            $achievements = [];

            if($friends->response->count > 50 && $friends->response->count < 100) {
                $achievements['name'] = 'Друзья и знакомые';
                $achievements['make'] = 'Добавить 50 и более друзей';

            } elseif($friends->response->count > 100 && $friends->response->count < 500 ) {
                $achievements['name'] = 'Общительный';
                $achievements['make'] = 'Добавить 100 и более друзей';

            } elseif($friends->response->count > 500 && $friends->response->count < 1000 ) {
                $achievements['name'] = 'Куча друзей';
                $achievements['make'] = 'Добавить 500 и более друзей';

            } elseif($friends->response->count > 1000) {
                $achievements['name'] = 'Самый популярный';
                $achievements['make'] = 'Добавить 1000 и более друзей';

            }
            
            return [
    
                'friends'  => [
    
                    'count'         => $friends->response->count,
                    'achievements'  => $achievements
                ]
           ];
        }
    
        public function query($method,$fields) {
    
            $query = json_decode(file_get_contents("https://api.vk.com/method/{$method}?{$fields}&v=5.52&access_token={$this->M_user->get($this->session->uid)->token}"));
    
            if(isset($query->error)) {
                
                switch($query->error->error_code) {
    
                    case 15: return $query->error->error_msg;
                        break;
                }
            }
    
            return $query;
        }
}