<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, GET, DELETE, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once(__DIR__ . "/utils.php");
$conn = dbConnect();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        if (!isset($data->account_id)) {
            echo json_encode(["error" => "account_id mancante"]);
            exit();
        }

        $stmt = $conn->prepare("INSERT INTO shoppingbag (account_id) VALUES (?)");
        $stmt->bind_param("i", $data->account_id);
        if ($stmt->execute()) {
            echo json_encode(["shoppingbag_id" => $stmt->insert_id]);
        } else {
            echo json_encode(["error" => "Errore nella creazione del carrello"]);
        }
        break;

    case 'GET':
        if (!isset($_GET['shoppingbag_id'])) {
            echo json_encode(["error" => "shoppingbag_id mancante"]);
            exit();
        }

        $bagId = $_GET['shoppingbag_id'];

        $stmt = $conn->prepare("
            SELECT s.product_id, p.name, p.price, s.quantity
            FROM shoppingbag_row s
            JOIN products p ON s.product_id = p.id
            WHERE s.shoppingbag_id = ?
        ");
        $stmt->bind_param("i", $bagId);
        $stmt->execute();
        $result = $stmt->get_result();
        $items = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode($items);
        break;

    case 'DELETE':
        if (!isset($_GET['shoppingbag_id'])) {
            echo json_encode(["error" => "shoppingbag_id mancante"]);
            exit();
        }

        $bagId = $_GET['shoppingbag_id'];

        $conn->query("DELETE FROM shoppingbag_row WHERE shoppingbag_id = $bagId");
        $conn->query("DELETE FROM shoppingbag WHERE id = $bagId");

        echo json_encode(["message" => "Carrello eliminato"]);
        break;

    
}
?>
