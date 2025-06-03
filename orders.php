<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
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
        $shoppingbagId = $data->shoppingbag_id ?? null;
        $accountId = $data->account_id ?? null;

        if (!$shoppingbagId || !$accountId) {
            echo json_encode(["error" => "Dati mancanti"]);
            exit();
        }

        
        $stmt = $conn->prepare("INSERT INTO orders (account_id) VALUES (?)");
        $stmt->bind_param("i", $accountId);
        $stmt->execute();
        $orderId = $stmt->insert_id;

       
        $query = "
            INSERT INTO order_row (order_id, product_id, quantity, price)
            SELECT ?, product_id, quantity, 
                   (SELECT price FROM products WHERE id = s.product_id)
            FROM shoppingbag_row s
            WHERE shoppingbag_id = ?
        ";
        $copy = $conn->prepare($query);
        $copy->bind_param("ii", $orderId, $shoppingbagId);
        $copy->execute();

       
        $conn->query("DELETE FROM shoppingbag_row WHERE shoppingbag_id = $shoppingbagId");
        $conn->query("DELETE FROM shoppingbag WHERE id = $shoppingbagId");

        echo json_encode(["message" => "Ordine creato", "order_id" => $orderId]);
        break;

    case 'GET':
        $accountId = $_GET['account_id'] ?? null;
        if (!$accountId) {
            echo json_encode(["error" => "account_id mancante"]);
            exit();
        }

        $query = "
            SELECT o.id AS order_id, o.created_at, p.name, r.quantity, r.price
            FROM orders o
            JOIN order_row r ON o.id = r.order_id
            JOIN products p ON r.product_id = p.id
            WHERE o.account_id = ?
            ORDER BY o.created_at DESC
        ";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $accountId);
        $stmt->execute();
        $result = $stmt->get_result();
        $orders = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode($orders);
        break;

    
}
?>
