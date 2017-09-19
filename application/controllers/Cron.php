<?php

class Cron extends CI_Controller 
{
    private $code = 'TbhjkQzzGnn';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('M_cron');
    }

    // $code - специальный код, чтобы предотвратить доступ к этой странице сторонним пользователям

    public function update_user_friends(string $code = '')
    {
        if($code !== '' && $code === $this->code) {

            $this->M_cron->update_user_friends();

        } else {

            show_error('Ошибка доступа', '400', $heading = 'An Error Was Encountered');

        }
    }
}