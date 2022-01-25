<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Site extends CI_Controller {

    const EMAILS = 'claudiu@aplicatieweb.ro, ionut@aplicatieweb.ro, office@aplicatieweb.ro, ionutytaly32@gmail.com, claudiumorogan@gmail.com';

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
    }

    public function index()

    {        

        $data['page'] = 'home';

        
        $this->load->view('site/header.php', $data);

        $this->load->view('site/pages/home.php');

        $this->load->view('site/footer.php');

    }

    /**

     * Pagina de contact

     */

    public function contact()

    {

        $data['page'] = 'contact';

        $this->load->view('site/header.php', $data);

        $this->load->view('site/pages/contact.php');

        $this->load->view('site/footer.php');

    }



    /**

     * Pagina de portofoliu

     */

    public function portofoliu()

    {

        // Incarcam site-urile pe care le-am facut

        // Cauta functia loadProjects ca sa modifici sau sa adaugi proiecte

        $data['projects'] = $this->loadProjects();

        $data['page']     = 'portofoliu';

        

        $this->load->view('site/header.php', $data);

        $this->load->view('site/pages/portofoliu.php', $data);

        $this->load->view('site/footer.php');        

    }



    



    /**

     * Pagina de servicii     

     */

    public function servicii()

    {

        $data['page']     = 'servicii';



        $this->load->view('site/header.php', $data);

        $this->load->view('site/pages/servicii.php', $data);

        $this->load->view('site/footer.php');        

    }



    /**

     * Pagina despre noi     

     */

    public function desprenoi()

    {



        $data['page']     = 'despre-noi';



        $this->load->view('site/header.php', $data);

        $this->load->view('site/pages/despre-noi.php', $data);

        $this->load->view('site/footer.php');        

    }



    public function formular_comanda($tip = false)

    {

        $data['page'] = 'formular_comanda';



        switch ($tip) {

            case 'site-prezentare-basic':

                $data['subject'] = "Cerere site de prezentare";

                break;

            

            case 'site-prezentare-standard':

                $data['subject'] = "Cerere site de prezentare";

                break;



            case 'site-prezentare-professional':

                $data['subject'] = "Cerere site de prezentare";

                break;



            case 'site-prezentare-ultimate':

                $data['subject'] = "Cerere site de prezentare";

                break;



            case 'magazin-basic':

                $data['subject'] = "Cerere magazin virtual";

                break;



            case 'magazin-standard':

                $data['subject'] = "Cerere magazin virtual";

                break;



            case 'magazin-professional':

                $data['subject'] = "Cerere magazin virtual";

                break;



            case 'magazin-ultimate':

                $data['subject'] = "Cerere magazin virtual";

                break;



            case 'mentenanta_web':

                $data['subject'] = 'Cerere mentenanta web';

                break;



            case 'reparare_pc':

                $data['subject'] = 'Cerere reparatie PC';

                break;



            case 'alte_servicii':

                $data['subject'] = 'Cerere servicii diverse';

                break;







            default:

                $data['subject'] = '';

                break;

        }



        $data['tip_site'] = $tip;



        $this->load->view('site/header', $data);

        $this->load->view('site/pages/formular_comanda', $data);

        $this->load->view('site/footer');



    }



    /**

     * Metoda de trimitere email, de pe pagina de contact

     */

    public function contactAjax()

    {

        $this->load->library('email');
        
        // Colectez datele din formular

        $nume    = $this->input->post('name');

        $email   = $this->input->post('email');

        $subiect = $this->input->post('subject');

        $mesaj   = $this->input->post('message');



        $this->email->to(RO::EMAILS);

        $this->email->from($email, $nume);

        $this->email->subject($subiect);

        $this->email->message($mesaj);



        if ($this->email->send())

        {

            echo json_encode('Trimis');

        } else

        {

            echo json_encode('Netrimis');

        }

    }



    /**

     * Incarcare proiecte

     * @return array - Vector cu toate 

     */

    protected function loadProjects()

    {

        $projects = $this->loadXML();

        return (!empty($projects)) ? $projects : false;

    }



    protected function loadXML()

    {

        $xml = new DOMDocument();



        $fileUrl     = site_url()."xml/proiecte.xml";

        $count       = 0;

        $projectsArr = [];



        $xml->load($fileUrl);

        $proiecte = $xml->getElementsByTagName('proiect');



        foreach($proiecte as $proiect)

        {

            

            $name      = $this->extrageDinXML('denumire', $proiect);

            $type      = $this->extrageDinXML('tip', $proiect);

            $imageLink = $this->extrageDinXML('imagine', $proiect);            

            $searchF   = $this->extrageDinXML('filtru', $proiect);            

            $url       = $this->extrageDinXML('link', $proiect);            



            $container = new stdClass();

            $container->nume    = $name;

            $container->tip     = $type;

            $container->imagine = site_url().$imageLink;

            $container->filtru  = $searchF;

            $container->link    = $url;



            $projectsArr[$count] = $container;

            

            $count++;

        }



        return (!empty($projectsArr)) ? $projectsArr : false;



    }



    protected function extrageDinXML($camp, $obj)

    {

        $x = $obj->getElementsByTagName($camp);

        $x = $x->item(0)->nodeValue;



        return $x;

    }
	

    /**

     * Trimite mail cu informatiile de pe formularul de contact din footer

     */

	public function quickContact()

    {

        $this->load->library('email');



        // Colectez datele din formular

        $nume    = $this->input->post('name');

        $email   = $this->input->post('email');

        $subiect = 'Mesaj de pe pagina principala';

        $mesaj   = $this->input->post('message');



        $this->email->to(RO::EMAILS);

        $this->email->from($email, $nume);

        $this->email->subject($subiect);

        $this->email->message($mesaj);



        $this->email->send();



        redirect(site_url());

    }



    /**

     * Trimitere mail din formularul de contact de pe pagina de contact

     */

    public function contact_form()

    {

        $this->load->library('email');



        // Colectez datele din formularul de contact de pe pagina de contact

        $nume    = $this->input->post('name');

        $email   = $this->input->post('email');

        $subiect = $this->input->post('subject');

        $mesaj   = $this->input->post('message');



        // Setari pentru mailul trimis

        $this->email->to(RO::EMAILS);

        $this->email->from($email, $nume);

        $this->email->subject($subiect);

        $this->email->message($mesaj);



        // Trimitere email

        $this->email->send();



        redirect(site_url()."ro/contact");

    }

	

	public function ana()

    {

        $data['page']     = 'Ana Maria Moiceanu';

        $this->load->view('site/pages/ana.php', $data);

    }


}