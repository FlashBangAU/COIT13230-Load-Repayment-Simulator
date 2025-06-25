<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['id-user'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

// Include the database connection
require('../db-connection.php');  // Corrected path to the db-connection.php file

require_once("../button-functions.php");

// Handle different HTTP request methods
switch ($_SERVER['REQUEST_METHOD']) {

    case 'GET':
        // User is already validated at the top
        $ID_user = (int)$_SESSION['id-user'];

        $DB_set = $_GET['db_set'] ?? null;

        if (!$ID_user || !$DB_set) {
            http_response_code(400);
            echo "Missing user_id or db_set";
            exit;
        }

        $query = "SELECT * FROM additional_payments WHERE ID_user = ? AND DB_set = ? ORDER BY date_additional_payment";
        $stmt = $db->prepare($query);
        $stmt->bind_param("ii", $ID_user, $DB_set);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['date_additional_payment']) . "</td>";
            echo "<td>$" . htmlspecialchars($row['amount_additional_payments']) . "</td>";
            echo "<td><input type='checkbox' name='updatePMT' class='form-check-input' " . ($row['update_PMT'] == 1 ? "checked" : "") . " disabled></td>";
            
            // Your PHP button functions output buttons inside <td> cells
            editBtn("edit-payment-btn", $DB_set, $row['payment_ID'], $row['date_additional_payment'], $row['amount_additional_payments'], $row['update_PMT'], "Edit");
            deleteBtn("delete-payment-btn", $DB_set, $row['payment_ID'], $row['date_additional_payment'], $row['amount_additional_payments'], $row['update_PMT'], "Delete");

            echo "</tr>";
        }

        $stmt->close();
        $result->free();
        $db->close();

    break;



    case 'POST':
    // Get raw JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode([
            "success" => false,
            "message" => "Invalid JSON input: " . json_last_error_msg()
        ]);
        exit;
    }

    // Validate required fields
    if (
        isset($input['ID_user'], $input['DB_set'], $input['date_additional_payment'],
              $input['amount_additional_payments'], $input['update_PMT'])
    ) {
        $ID_user = (int)$input['ID_user'];
        $DB_set = (int)$input['DB_set'];
        $date_additional_payment = $input['date_additional_payment'];
        $amount_additional_payments = (float)$input['amount_additional_payments'];
        $update_PMT = (int)$input['update_PMT'];

        // Get the next available payment_ID
        $queryNextPaymentId = "
            SELECT MIN(payment_ID) + 1 AS next_payment_ID
            FROM (
                SELECT payment_ID
                FROM additional_payments
                WHERE ID_user = ? AND DB_set = ?
                UNION ALL
                SELECT 0
            ) AS temp
            WHERE (payment_ID + 1) NOT IN (
                SELECT payment_ID
                FROM additional_payments
                WHERE ID_user = ? AND DB_set = ?
            )
            LIMIT 1;
        ";

        $stmtNextPaymentId = $db->prepare($queryNextPaymentId);
        if ($stmtNextPaymentId) {
            $stmtNextPaymentId->bind_param("iiii", $ID_user, $DB_set, $ID_user, $DB_set);
            $stmtNextPaymentId->execute();
            $stmtNextPaymentId->bind_result($nextPaymentID);
            $stmtNextPaymentId->fetch();
            $stmtNextPaymentId->close();

            if (is_null($nextPaymentID)) {
                echo json_encode([
                    "success" => false,
                    "message" => "Could not determine the next payment_ID."
                ]);
                $db->close();
                exit;
            }
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Failed to prepare nextPaymentID query: " . $db->error
            ]);
            $db->close();
            exit;
        }

        // Insert the new payment
        $query = "
            INSERT INTO additional_payments (
                ID_user, DB_set, payment_ID, date_additional_payment,
                amount_additional_payments, update_PMT
            ) VALUES (?, ?, ?, ?, ?, ?)
        ";

        $stmt = $db->prepare($query);
        $stmt->bind_param(
            "iiisdi",
            $ID_user, $DB_set, $nextPaymentID,
            $date_additional_payment, $amount_additional_payments, $update_PMT
        );

        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo json_encode([
                "success" => true,
                "message" => "Payment added successfully"
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Failed to add payment"
            ]);
        }

        $stmt->close();
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Missing required fields"
        ]);
    }
    break;

    case 'PUT':
    // Read raw JSON input
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true);

    // 🔧 Log input for debugging
    file_put_contents('log.txt', print_r($input, true), FILE_APPEND);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode([
            "success" => false,
            "message" => "Invalid JSON: " . json_last_error_msg()
        ]);
        exit;
    }

    // Validate required fields
    if (
        isset(
            $input['ID_user'],
            $input['DB_set'],
            $input['payment_ID'],
            $input['date_additional_payment'],
            $input['amount_additional_payments'],
            $input['update_PMT']
        )
    ) {
        // Extract and sanitise input
        $ID_user = (int)$input['ID_user'];
        $DB_set = (int)$input['DB_set'];
        $payment_ID = (int)$input['payment_ID'];
        $date_additional_payment = $input['date_additional_payment'];              // string
        $amount_additional_payments = (float)$input['amount_additional_payments']; // float
        $update_PMT = (int)$input['update_PMT'];

        // 🔧 Check if record exists before attempting update
        $checkQuery = "
            SELECT * FROM additional_payments
            WHERE ID_user = ? 
              AND DB_set = ? 
              AND payment_ID = ?
        ";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bind_param('iii', $ID_user, $DB_set, $payment_ID);
        $checkStmt->execute();
        $result = $checkStmt->get_result();

        if ($result->num_rows === 0) {
            echo json_encode([
                "success" => false,
                "message" => "No matching payment found"
            ]);
            exit;
        }

        // Prepare SQL update
        $query = "
            UPDATE additional_payments
               SET date_additional_payment = ?,
                   amount_additional_payments = ?,
                   update_PMT = ?
             WHERE ID_user = ? 
               AND DB_set  = ?
               AND payment_ID = ?
        ";
        $stmt = $db->prepare($query);

        if (!$stmt) {
            echo json_encode([
                "success" => false,
                "message" => "Failed to prepare SQL query"
            ]);
            exit;
        }

        $stmt->bind_param(
            'sdiiii',
            $date_additional_payment,    // s = string
            $amount_additional_payments, // d = double
            $update_PMT,                 // i = integer
            $ID_user,                    // i = integer
            $DB_set,                     // i = integer
            $payment_ID                  // i = integer
        );

        if (!$stmt->execute()) {
            echo json_encode([
                "success" => false,
                "message" => "Failed to execute query: " . $stmt->error
            ]);
            exit;
        }

        // 🔧 Separate case: no changes vs successful update
        if ($stmt->affected_rows > 0) {
            echo json_encode([
                "success" => true,
                "message" => "Payment updated successfully"
            ]);
        } else {
            echo json_encode([
                "success" => true,  // still technically successful
                "message" => "No changes made — data may be identical to current values"
            ]);
        }

        $stmt->close();
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Missing required fields"
        ]);
    }
    break;


    case 'DELETE':
        $input = json_decode(file_get_contents("php://input"), true);

        if (!isset($_SESSION['id-user'])) {
            echo json_encode(["success" => false, "message" => "User not logged in"]);
            exit;
        }

        $ID_user = (int)$_SESSION['id-user'];
        $payment_ID = isset($input['payment_ID']) ? (int)$input['payment_ID'] : 0;
        $DB_set = isset($input['DB_set']) ? (int)$input['DB_set'] : 0;

        if ($payment_ID && $DB_set) {
            $query = "DELETE FROM additional_payments WHERE ID_user = ? AND DB_set = ? AND payment_ID = ?";
            $stmt = $db->prepare($query);
            $stmt->bind_param("iii", $ID_user, $DB_set, $payment_ID);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                echo json_encode(["success" => true, "message" => "Payment deleted successfully"]);
            } else {
                echo json_encode(["success" => false, "message" => "Payment not found or failed to delete"]);
            }

            $stmt->close();
        } else {
            echo json_encode(["success" => false, "message" => "Missing DB_set or payment_ID"]);
        }
    exit;

    break;


    default:
        // If the method is not one of the above, return an error
        echo json_encode(["error" => "Invalid request method"]);
        break;
}
?>
