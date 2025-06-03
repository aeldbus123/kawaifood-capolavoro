<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: PUT, DELETE, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once(__DIR__ . "/utils.php");
$conn = dbConnect();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'PUT':
        parse_str(file_get_contents("php://input"), $_PUT);

        $bagId = $_PUT['shoppingbag_id'] ?? null;
        $productId = $_PUT['product_id'] ?? null;
        $quantity = $_PUT['quantity'] ?? null;

        if (!$bagId || !$productId || !$quantity) {
            echo json_encode(["error" => "Dati incompleti"]);
            exit();
        }

        
        $check = $conn->prepare("SELECT id FROM shoppingbag_row WHERE shoppingbag_id = ? AND product_id = ?");
        $check->bind_param("ii", $bagId, $productId);
        $check->execute();
        $checkResult = $check->get_result();

        if ($checkResult->num_rows > 0) {
            $row = $checkResult->fetch_assoc();
            $stmt = $conn->prepare("UPDATE shoppingbag_row SET quantity = ? WHERE id = ?");
            $stmt->bind_param("ii", $quantity, $row['id']);
        } else {
            $stmt = $conn->prepare("INSERT INTO shoppingbag_row (shoppingbag_id, product_id, quantity) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $bagId, $productId, $quantity);
        }

        if ($stmt->execute()) {
            echo json_encode(["message" => "Prodotto aggiornato"]);
        } else {
            echo json_encode(["error" => "Errore nel salvataggio"]);
        }
        break;

    case 'DELETE':
        parse_str(file_get_contents("php://input"), $_DELETE);

        $bagId = $_DELETE['shoppingbag_id'] ?? null;
        $productId = $_DELETE['product_id'] ?? null;

        if (!$bagId || !$productId) {
            echo json_encode(["error" => "Dati incompleti"]);
            exit();
        }

        $stmt = $conn->prepare("DELETE FROM shoppingbag_row WHERE shoppingbag_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $bagId, $productId);
        $stmt->execute();

        echo json_encode(["message" => "Prodotto rimosso"]);
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Metodo non supportato"]);
        break;
}
?>
