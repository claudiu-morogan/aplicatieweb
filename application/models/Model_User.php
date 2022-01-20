<?php

class Model_User extends CI_Model
{

	// Denumirea tabelei de utilizatori
	const TABELA_UTILIZATORI = 'utilizatori';
	const TABELA_TASKURI     = 'taskuri';

	/**
	 * Metoda de construire a obiectului
	 */
	public function __construct()
	{
		parent::__construct();		
	}

	public function verificaUtilizator($utilizator)
	{
		// Desfac utilizatorul ca sa am un acces facil la datele lui
		$user 	= $utilizator['user'];
		$parola = $utilizator['parola'];

		// Caut in baza de date utilizatorul
		$this->db->where('username', $user);
		$this->db->where('parola', md5($parola));
		$this->db->from(Model_User::TABELA_UTILIZATORI);

		// Vad cate inregistrari sunt in baza de date
		$nr = $this->db->count_all_results();
		
		// Daca gaseste FIX un utilizator inseamna ca e valid ce am primit si dau unda verde sa intre in aplicatie 
		if($nr == 1)
			return true;

		// Am gasit 0 inregistrari si daca gasesc mai mult de o inregistrare inseamna ca am mai mult de un utilizator cu acelasi username si parola si am o buseala
		// in baza de date, si trebuie sa o tratez ca pe o eroare
		return false;
	}

	public function incarcaIDUtilizator($username)
	{
		$this->db->select('id as id_utilizator');
		$this->db->where('username', $username);
		$this->db->from(Model_User::TABELA_UTILIZATORI);

		$rezultat = $this->db->get();
		$rezultat = $rezultat->result();
		if(!empty($rezultat))
		{
			$rezultat = $rezultat[0]->id_utilizator;
		} else {
			$rezultat = false;
		}

		return $rezultat;

	}

	public function calculeazaOreLuna($id_utilizator, $luna)
	{
		$this->db->select('SUM(ore_finalizare) as ore_totale');		
		$this->db->from(Model_User::TABELA_TASKURI);		
		$this->db->where('id_utilizator', $id_utilizator);
		$this->db->where('MONTH(data_creat)', $luna);

		$rezultat = $this->db->get();
		$rezultat = $rezultat->result();
		if(!empty($rezultat[0]->ore_totale)){
			$rezultat = $rezultat[0]->ore_totale;	
		} else {
			$rezultat = 0;
		}	
		return $rezultat;
	}
}