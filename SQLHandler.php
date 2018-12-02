<?php
class SQLHandler{

    /*
    database parameters
    */
    private $conn;

    const SERVERNAME = 'localhost';
    const USERNAME = 'root';
    const PASSWORD = 'password';
    const DATABASE = 'Note_Passing';

    public function __construct(){
        $this->conn = new mysqli(self::SERVERNAME, self::USERNAME, self::PASSWORD, self::DATABASE);
        if($this->conn->connect_error){
          die("Connection failed: " . $this->conn->connect_error());
        }
    }

    public function __destruct(){
      unset($this->conn);
    }

    public function getConn(){
      return $this->conn;
    }

    public function getAllTo($user){
      $UID = $this->conn->query("SELECT UID FROM user WHERE name='$user'")->fetch_assoc()['UID'];
      $result = $this->conn->query("SELECT * FROM note WHERE Uto='$UID'");
      $results = array();
      if($result->num_rows > 0){
        while($row = $result->fetch_assoc()){
          $results[] = $row;
        }
      }
      return $results;
    }

    public function addNote($Ufrom, $Uto, $text){
      $fromID = $this->conn->query("SELECT UID FROM user WHERE name='$Ufrom'")->fetch_assoc()['UID'];
      $toID = $this->conn->query("SELECT UID FROM user WHERE name='$Uto'")->fetch_assoc()['UID'];

      $this->conn->query("INSERT INTO note(Ufrom, Uto, text) VALUES('$fromID', '$toID', '$text')");
      echo "INSERT INTO note(Ufrom, Uto, text) VALUES('$fromID', '$toID', '$text')";
      echo mysqli_error($this->conn);
    }
}

header("Content-type: text/html; charset=utf-8");
ini_set('display_errors', 1);
ini_set('displat_startup_errors', 1);
error_reporting(E_ALL);

$db = new SQLHandler();
$action = $_POST['action'];
if($action === "getAllTo"){
  echo JSON_encode($db->getAllTo($_POST['user']));
}else if($action === 'addNote'){
  $db->addNote($_POST['Ufrom'], $_POST['Uto'], $_POST['text']);
}
?>
