<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['id-user'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

require('../db-connection.php');
require_once("../button-functions.php");

$ID_user = (int)$_SESSION['id-user'];

switch ($_SERVER['REQUEST_METHOD']) {

    case 'GET':
        if (!$ID_user) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Missing user_id"]);
            exit;
        }

        if (isset($_GET['DB_set'])) {
            $DB_set = (int)$_GET['DB_set'];
            $query = "SELECT * FROM starting_loan_values WHERE ID_user = ? AND DB_set = ?";
            $stmt = $db->prepare($query);
            $stmt->bind_param("ii", $ID_user, $DB_set);
        } else {
            $query = "SELECT * FROM starting_loan_values WHERE ID_user = ? ORDER BY DB_set";
            $stmt = $db->prepare($query);
            $stmt->bind_param("i", $ID_user);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $loans = $result->fetch_all(MYSQLI_ASSOC);

        echo json_encode($loans);

        $stmt->close();
        $db->close();
        break;

    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Invalid JSON: " . json_last_error_msg()]);
            exit;
        }

        if (isset($input['start_date'], $input['start_interest'], $input['start_principle'], $input['duration_years'], $input['payment_interval'])) {
            $start_date = $input['start_date'];
            $start_interest = (float)$input['start_interest'];
            $start_principle = (float)$input['start_principle'];
            $duration_years = (int)$input['duration_years'];
            $payment_interval = $input['payment_interval'];

            $q = "
                SELECT MIN(DB_set) + 1 AS next_DB_set
                FROM (
                    SELECT DB_set FROM starting_loan_values WHERE ID_user = ?
                    UNION ALL SELECT 0
                ) AS temp
                WHERE (DB_set + 1) NOT IN (
                    SELECT DB_set FROM starting_loan_values WHERE ID_user = ?
                )
                LIMIT 1;
            ";

            $stmt = $db->prepare($q);
            $stmt->bind_param("ii", $ID_user, $ID_user);
            $stmt->execute();
            $stmt->bind_result($nextDB_set);
            $stmt->fetch();
            $stmt->close();

            if (!$nextDB_set) {
                echo json_encode(["success" => false, "message" => "Could not determine next DB_set"]);
                $db->close();
                exit;
            }

            $q = "
                INSERT INTO starting_loan_values 
                (ID_user, DB_set, start_date, start_interest, start_principle, duration_years, payment_interval)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ";

            $stmt = $db->prepare($q);
            $stmt->bind_param("iisddis", $ID_user, $nextDB_set, $start_date, $start_interest, $start_principle, $duration_years, $payment_interval);

            if (!$stmt->execute()) {
                echo json_encode(["success" => false, "message" => "Insert failed: " . $stmt->error]);
            } else {
                echo json_encode(["success" => true, "message" => "Loan added successfully"]);
            }

            $stmt->close();
        } else {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Missing required fields"]);
        }

        $db->close();
        break;

    case 'PUT':
        $input = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Invalid JSON: " . json_last_error_msg()]);
            exit;
        }

        if (isset($input['DB_set'], $input['start_date'], $input['start_interest'], $input['start_principle'], $input['duration_years'], $input['payment_interval'])) {
            $DB_set = (int)$input['DB_set'];
            $start_date = $input['start_date'];
            $start_interest = (float)$input['start_interest'];
            $start_principle = (float)$input['start_principle'];
            $duration_years = (int)$input['duration_years'];
            $payment_interval = $input['payment_interval'];

            $q = "
                UPDATE starting_loan_values
                SET start_date = ?, start_interest = ?, start_principle = ?, duration_years = ?, payment_interval = ?
                WHERE ID_user = ? AND DB_set = ?
            ";

            $stmt = $db->prepare($q);
            $stmt->bind_param("sddisii", $start_date, $start_interest, $start_principle, $duration_years, $payment_interval, $ID_user, $DB_set);

            if (!$stmt->execute()) {
                echo json_encode(["success" => false, "message" => "Update failed: " . $stmt->error]);
            } else if ($stmt->affected_rows > 0) {
                echo json_encode(["success" => true, "message" => "Loan updated successfully"]);
            } else {
                echo json_encode(["success" => false, "message" => "No changes made or loan not found"]);
            }

            $stmt->close();
        } else {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Missing required fields"]);
        }

        $db->close();
        break;

    case 'DELETE':
        $input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE || !isset($input['DB_set'])) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Invalid JSON or missing DB_set"]);
            exit;
        }

        $DB_set = (int)$input['DB_set'];

        $q = "DELETE FROM starting_loan_values WHERE ID_user = ? AND DB_set = ?";
        $stmt = $db->prepare($q);
        $stmt->bind_param("ii", $ID_user, $DB_set);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo json_encode(["success" => true, "message" => "Loan deleted successfully"]);
        } else {
            echo json_encode(["success" => false, "message" => "Loan not found or failed to delete"]);
        }

        $stmt->close();
        $db->close();
        break;

    default:
        http_response_code(405);
        echo json_encode(["success" => false, "message" => "Invalid request method"]);
        break;
}
?>