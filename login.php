<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();
require_once(__DIR__ . "/utils.php");
$conn = dbConnect();

$data = json_decode(file_get_contents("php://input"));

if (isset($data->username) && isset($data->password)) {
    $user = $data->username;
    $psw = $data->password;

    
    $stmt = $conn->prepare("SELECT id, username FROM accounts WHERE username=? AND password=?");
    $stmt->bind_param("ss", $user, $psw);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION["username"] = $row["username"];
        echo json_encode([
            "code" => 1,
            "username" => $row["username"],
            "account_id" => $row["id"], 
            "message" => "Login riuscito"
        ]);
        exit();
    } else {
        echo json_encode(["code" => 0, "message" => "Credenziali errate"]);
        exit();
    }
} else {
    if (isset($_SESSION["username"])) {
        echo json_encode(["code" => 1, "username" => $_SESSION["username"]]);
    } else {
        echo json_encode(["code" => 0, "message" => "Dati mancanti"]);
    }
}
?>
