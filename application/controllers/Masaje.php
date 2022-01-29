<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Masaje extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database('default');
    }

    public function index()
    {
        $this->load->library('Grocery_CRUD');

        $crud = new Grocery_CRUD();
        $crud->set_theme('bootstrap');
        $crud->set_table('app_masaje');

        $crud->set_relation('client_id', 'app_masaj_clienti', '{prenume} {nume}');
        $crud->display_as('client_id', 'Client');


        $output = $crud->render();
        $this->load->view('gc_output',$output);

    }

    public function clienti()
    {
        $this->load->library('Grocery_CRUD');

        $crud = new Grocery_CRUD();
        $crud->set_theme('bootstrap');
        $crud->set_table('app_masaj_clienti');

        $output = $crud->render();
        $this->load->view('gc_output',$output);

    }
}