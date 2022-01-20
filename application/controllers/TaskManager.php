<?php

defined('BASEPATH') OR exit('No direct script access allowed');



class TaskManager extends CI_Controller

{

    public function __construct()

    {

        parent::__construct();



        $this->load->model('Model_User');

        $this->load->library('session');

        $this->load->library('encryption');

        $this->load->helper('url');       



    }



    public function protect()

    {      

        // Incarc statusul utilizatorului, daca este logat sau nu

        $logat = TaskManager::verificaLogare();



        // verific daca nu este logat

        if(!$logat)

        {

            // Redirectare catre pagina de logare

            redirect(site_url().'taskmanager/logare');        

        }



    }



    public function logare()

    {       
    	

        // Verific daca s-a dat submit la formularul de logare

        if($_POST)

        {

            // Colectez valorile introduse de utilizator in formularul de logare

            $username   = $this->input->post('username');

            $parola     = $this->input->post('password');



            // Creez un vector cu proprietatile unui utilizator

            $utilizator = [];

            $utilizator['user']   = $username;

            $utilizator['parola'] = $parola;            



            // Primesc un raspuns de true sau fals

            $utilizatorValid = $this->Model_User->verificaUtilizator($utilizator);            



            // Verific daca utilizatorul este valid

            if($utilizatorValid)

            {

                // Success la autentificare, si parcurg urmatorii pasi

                $arrData = [

                    'utilizatorLogat' => 1,

                    'utilAssp'        => $this->encryption->encrypt($parola),

                    'user'            => $username

                ];



                // Stochez intr-o sesiune datele din vectorul de mai sus, pe care sa le am accesibile in toata aplicatia

                $this->session->set_userdata($arrData);

                redirect('/taskmanager');



            } else {

                // Autentificare esuata, reincearca gogule !

                redirect('taskManager/logare');

            } // sfarsit validare autentificare

        } // sfarsit verificare POST

        

        $this->load->view('taskManager/login');

    }



    public function logout()

    {

        $this->session->sess_destroy();

        redirect('/taskmanager');

    }



    public function initializareCRUD()

    {

        TaskManager::protect();  







        $this->load->helper('url');

        $this->load->library('Grocery_CRUD');



        $crud = new Grocery_CRUD();



        $crud->set_theme('bootstrap');

        $crud->set_language('romanian');



        $crud->set_table('taskuri');

        $crud->fields('id_utilizator', 'client', 'denumire', 'observatii', 'data_limita','ore_finalizare', 'prioritate', 'status', 'data_creat');

        $crud->field_type('data_creat', 'hidden');

        $crud->set_relation('id_utilizator', 'utilizatori', '{prenume} - {nume}');



        

        $crud->field_type('denumire', 'string');

        $crud = $this->seteazaDenumiri($crud);

        $crud = $this->eliminaEditorText($crud);        



        return $crud;

    }



    public function index()

    {   

        TaskManager::protect();

        

        $username_logat = $this->session->userdata('user');

        $id_utilizator = $this->Model_User->incarcaIDUtilizator($username_logat);



        $crud = $this->initializareCRUD();



        $crud->columns('client', 'prioritate', 'denumire', 'data_limita', 'observatii');



        $crud->callback_before_insert(array($this, 'adaugaDataTaskului'));

        $crud->callback_column('prioritate', array($this, 'seteazaCulorile'));

        $crud->callback_column('data_limita', array($this, 'alertData'));        



        $crud->order_by('prioritate', 'asc');



        $crud->where('id_utilizator', $id_utilizator);

        $crud->where('status', 'nefinalizat');





        $output = $crud->render();

        $output->oreLunare = $this->oreLunare($id_utilizator);



        $this->load->view('taskManager/output',$output);

    }



    public function finalizateTotal()

    {

        TaskManager::protect();



        $username_logat = $this->session->userdata('user');

        $id_utilizator = $this->Model_User->incarcaIDUtilizator($username_logat);



        $crud = $this->initializareCRUD();



        $crud->columns('client', 'prioritate', 'denumire', 'data_limita', 'observatii', 'status');



        $crud->callback_column('prioritate', array($this, 'seteazaCulorile'));

        $crud->callback_column('data_limita', array($this, 'alertData'));     



        $crud->where('id_utilizator', $id_utilizator);

        $crud->where('status', 'finalizat');   



        $output = $crud->render();

        $output->oreLunare = $this->oreLunare($id_utilizator);



        $this->load->view("taskManager/output", $output);

    }



    public function finalizateLunar()

    {

        TaskManager::protect();



        $username_logat = $this->session->userdata('user');

        $id_utilizator = $this->Model_User->incarcaIDUtilizator($username_logat);



        $username_logat = $this->session->userdata('user');

        $id_utilizator = $this->Model_User->incarcaIDUtilizator($username_logat);



        $crud = $this->initializareCRUD();



        $crud->columns('client', 'prioritate', 'denumire', 'data_limita', 'observatii', 'status');



        $crud->callback_column('prioritate', array($this, 'seteazaCulorile'));

        $crud->callback_column('data_limita', array($this, 'alertData'));     



        $crud->where('id_utilizator', $id_utilizator);

        $crud->where('status', 'finalizat');   



        $crud->where('MONTH(data_creat)', date('m'));



        $output = $crud->render();

        $output->oreLunare = $this->oreLunare($id_utilizator);



        $this->load->view("taskManager/output", $output);

    }



    public function finalizatelunaruseri()

    {



        TaskManager::protect();



        $username_logat = $this->session->userdata('user');

        $id_utilizator = $this->Model_User->incarcaIDUtilizator($username_logat);

        

        $crud = $this->initializareCRUD();



        $crud->columns('client', 'prioritate', 'denumire', 'data_limita', 'observatii', 'status', 'ore_finalizare', 'id_utilizator');



        $crud->callback_column('prioritate', array($this, 'seteazaCulorile'));

        $crud->callback_column('data_limita', array($this, 'alertData'));     

                

        $crud->where('status', 'finalizat');   



        $crud->where('MONTH(data_creat)', date('m'));



        $output = $crud->render();

        $output->oreLunare = $this->oreLunare($id_utilizator);



        $this->load->view("taskManager/output", $output);



    }



    public function nefinalizateLunarUseri()

    {



        TaskManager::protect();



        $username_logat = $this->session->userdata('user');

        $id_utilizator = $this->Model_User->incarcaIDUtilizator($username_logat);

        

        $crud = $this->initializareCRUD();



        $crud->columns('client', 'prioritate', 'denumire', 'data_limita', 'observatii', 'status', 'ore_finalizare', 'id_utilizator');



        $crud->callback_column('prioritate', array($this, 'seteazaCulorile'));

        $crud->callback_column('data_limita', array($this, 'alertData'));     

                

        $crud->where('status', 'nefinalizat');   



        // $crud->where('MONTH(data_creat)', date('m'));



        $output = $crud->render();

        $output->oreLunare = $this->oreLunare($id_utilizator);



        $this->load->view("taskManager/output", $output);



    }



    protected function oreLunare($id_utilizator)

    {

        // incarc luna curenta

        $luna = date('m');        

        return $this->Model_User->calculeazaOreLuna($id_utilizator, $luna);

    }



    /**

     * Verifica daca data limita a fost depasita, si alerteaza userul

     *

     */

    public function alertData($value, $row)

    {

        TaskManager::protect();        

        if($row->data_limita < date('Y-m-d'))

            $row->data_limita = "<span class='urgent'>".$row->data_limita."</span>";

        return $row->data_limita;

    }



    /**

     * Converteste numarul de prioritate in denumire si seteaza culoarea in bootstrap

     *

     */



    public function seteazaCulorile($value, $row){

        TaskManager::protect();

        switch ($row->prioritate) {

            case '0':

                $row->prioritate = "<span class='urgent'>Urgenta</span>";        

                break;



            case '1':

                $row->prioritate = "<span class='rapid'>Rapida</span>";        

                break;



            case '2':

                $row->prioritate = "<span class='normal'>Normala</span>";        

                break;



            case '3':

                $row->prioritate = "<span class='scazut'>Scazuta</span>";        

                break;

            

            default:

                $row->prioritate = "";

                break;

        }



        return $row->prioritate;

    }



    /**

     * Introduce in baza de date data cand a fost adaugat task-u;

     */

    public function adaugaDataTaskului($post_array)

    {        

        $post_array['data_creat'] = date('Y-m-d');             

        return $post_array;

    }





    /**

     * Dezactivez editorul de tip Word in modulele de adaugare si modificare de taskuri

     *

     * @param      obiect  $crud   

     *

     * @return     obiect  $crud 

     */

    protected function eliminaEditorText($crud)

    {

        TaskManager::protect();

        if(!empty($crud)){

            $campuri = ['denumire', 'descriere'];

            foreach($campuri as $camp)

            {

                $crud->unset_texteditor($camp);

            }

            return $crud;

        }

            

    }



    /**

     * Redenumesc campurile afisate in aplicatie

     *

     * @param      obiect  $crud  

     *

     * @return     obiect  $crud

     */

    protected function seteazaDenumiri($crud)

    {

        TaskManager::protect();

        if(!empty($crud)){

            $denumiri = ['id_utilizator' => 'Responsabil task', 'data_creat' => 'Introducere in sistem', 'ore_finalizare' => 'Ore lucrate'];



            foreach($denumiri as $denActuala => $denumireNoua)

            {

                $crud->display_as($denActuala, $denumireNoua);

            }    

            return $crud;

        }



        return false;        

    }



    /**

     * { function_description }

     *

     * @return     boolean

     */

    public function verificaLogare()

    {

        if($this->session->userdata('utilizatorLogat') == 1)

            return true;

        return false;

    }

}