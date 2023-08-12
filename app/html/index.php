<?php

class DBConnection{

	protected $username;
	protected $password;
	protected $host;
    protected $db;

	public function __construct($loginCredentials)
	{
        
		$this->setUsername($loginCredentials['username']);
		$this->setPassword($loginCredentials['password']);
		$this->setHost($loginCredentials['host']);
        $this->setDatabase($loginCredentials['db']);


		$connection = ($this->connect()) ? $this->connect() : false;

		return $connection;
	}

	protected function connect()
	{
		return false;
	}

	protected function setUsername($user)
	{
		$this->username = $user;
	}

	protected function setPassword($pass)
	{
		$this->password = $pass;
	}

	protected function setHost($host)
	{
		$this->host = $host;
	}

    protected function setDatabase($db)
    {
        $this->db = $db;
    }

}

class MySql extends DBConnection
{

    protected $conn;

    public function connect()
    {        
        $mysqli = new mysqli($this->host,$this->username,$this->password,$this->db);

        // Check connection
        if ($mysqli -> connect_errno) {
          echo "Failed to connect to MySQL: " . $mysqli -> connect_error;
          exit();
        }

        $this->conn = $mysqli;        
    }

    public function count()
    {

        // Perform query
        if ($result = $this->conn-> query("SELECT * FROM test")) {
            echo "Returned rows are: " . $result -> num_rows;
            // Free result set
            $result -> free_result();
        }

    }

    public function show_registrations()
    {
        $db = $this->conn;
        $stmt = $db->prepare("SELECT * FROM test");
        $stmt->execute();
        $x = $stmt->fetch();

        while($row = $stmt->fetch() != false)
        {
            echo $row['id'];
        }
        
    }
}

$mysql = new MySql([
    'host'	   => 'db',
	'username' => 'claudiu2',
	'password' => 'claudiu2',
    'db'       => 'claudiu'	
]);

// $mysql->count();
$mysql->show_registrations();





