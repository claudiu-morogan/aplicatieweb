<?php

class DBConnection{

	protected $username;
	protected $password;
	protected $host;

	public function __construct($loginCredentials)
	{
		$this->setUsername($loginCredentials['username']);
		$this->setPassword($loginCredentials['password']);
		$this->setHost($loginCredentials['host']);

		$connection = ($this->connect()) ? $this->connect() : false;

		return $connection;
	}

	protected function connect()
	{
		return false;
	}

	protected function setUsername($user)
	{
		$this->username = $username;
	}

	protected function setPassword($pass)
	{
		$this->password = $pass;
	}

	protected function setHost($host)
	{
		$this->host = $host;
	}

}

class MySql extends DBConnection{}

$mysql = new MySql([
	'username' => 'root',
	'password' => '',
	'host'	   => 'db'
]);

var_dump($conn);

