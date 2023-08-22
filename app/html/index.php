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
        if ($result = $this->conn-> query("SELECT * FROM users")) {
            echo "Returned rows are: " . $result -> num_rows;
            // Free result set
            $result -> free_result();
        }

    }

    public function show_registrations()
    {
        // $db = $this->conn;
        // $stmt = $db->prepare("SELECT * FROM test");
        // $stmt->execute();
        
    }
}

$mysql = new MySql([
	'host'	   => 'db',
	'username' => 'claudiu',
	'password' => 'claudiu',
	'db'       => 'aplicatieweb'	
]);

if($_POST){
    echo 'post';
}
?>

<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
	<title>AplicatieWeb</title>
</head>
<body>
	<form action="index.php" method="POST">
		

    <div class="row">
        <div class="col-md-4">
            <!-- <p>Column 1</p> -->
        </div>
        <div class="col-md-4">
            
        <input type="text" class="form-control" name="username" placeholder="Username"><br>

		<input type="password" class="form-control" name="password" placeholder="Password"><br>

		<input type="submit" class="btn btn-danger pull-right" name="submit" value="Submit">
        </div>
        <div class="col-md-4">
            <!-- <p>Column 3</p> -->
        </div>
    </div>
    
        

	</form>
</body>
</html>






