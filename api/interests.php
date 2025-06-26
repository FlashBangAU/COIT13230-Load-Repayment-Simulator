<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['id-user'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

// Include the database connection
require('../db-connection.php');

require_once("../button-functions.php");

switch ($_SERVER['REQUEST_METHOD']) {


        case 'GET':
            $ID_user = (int)$_SESSION['id-user'];
            $DB_set = $_GET['db_set'] ?? null;

            if (!$ID_user || !$DB_set) {
                http_response_code(400);
                echo json_encode(["error" => "Missing user_id or db_set"]);
                exit;
            }

            $query = "SELECT * FROM interest_repayments WHERE ID_user = ? AND DB_set = ? ORDER BY date_interest";
            $stmt = $db->prepare($query);
            $stmt->bind_param("ii", $ID_user, $DB_set);
            $stmt->execute();
            $result = $stmt->get_result();

            $rows = [];
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }

            echo json_encode($rows);

            $stmt->close();
            $result->free();
            $db->close();
        break;



        case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode([
                "success" => false,
                "message" => "Invalid JSON input: " . json_last_error_msg()
            ]);
            exit;
        }

        if (
            isset($input['ID_user'], $input['DB_set'], $input['date_interest'],
                  $input['new_val_interest'], $input['update_PMT'])
        ) {
            $ID_user = (int)$input['ID_user'];
            $DB_set = (int)$input['DB_set'];
            $date_interest = $input['date_interest'];
            $new_val_interest = (float)$input['new_val_interest'];
            $update_PMT = (int)$input['update_PMT'];
            

            // Get the next available interest_ID
            $queryNextInterestId = "
                SELECT MIN(interest_ID) + 1 AS next_interest_ID
                FROM (
                    SELECT interest_ID
                    FROM interest_repayments
                    WHERE ID_user = ? AND DB_set = ?
                    UNION ALL
                    SELECT 0
                ) AS temp
                WHERE (interest_ID + 1) NOT IN (
                    SELECT interest_ID
                    FROM interest_repayments
                    WHERE ID_user = ? AND DB_set = ?
                )
                LIMIT 1;
            ";

            $stmtNextInterestId = $db->prepare($queryNextInterestId);
            if ($stmtNextInterestId) {
                $stmtNextInterestId->bind_param("iiii", $ID_user, $DB_set, $ID_user, $DB_set);
                $stmtNextInterestId->execute();
                $stmtNextInterestId->bind_result($nextInterestID);
                $stmtNextInterestId->fetch();
                $stmtNextInterestId->close();

                if (is_null($nextInterestID)) {
                    echo json_encode([
                        "success" => false,
                        "message" => "Could not determine the next interest_ID."
                    ]);
                    $db->close();
                    exit;
                }
            } else {
                echo json_encode([
                    "success" => false,
                    "message" => "Failed to prepare nextInterestID query: " . $db->error
                ]);
                $db->close();
                exit;
            }

            // Insert the new interest adjustment
            $query = "
                INSERT INTO interest_repayments (
                    ID_user, DB_set, interest_ID, date_interest,
                    new_val_interest, update_PMT
                ) VALUES (?, ?, ?, ?, ?, ?)
            ";

            $stmt = $db->prepare($query);
            $stmt->bind_param(
                "iiisdi",
                $ID_user, $DB_set, $nextInterestID,
                $date_interest, $new_val_interest, $update_PMT
            );

            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                echo json_encode([
                    "success" => true,
                    "message" => "Interest adjustment added successfully"
                ]);
            } else {
                echo json_encode([
                    "success" => false,
                    "message" => "Failed to add interest adjustment"
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
            $input['interest_ID'],
            $input['date_interest'],
            $input['new_val_interest'],
            $input['update_PMT']
        )
    ) {
        // Extract and sanitise input
        $ID_user = (int)$input['ID_user'];
        $DB_set = (int)$input['DB_set'];
        $interest_ID = (int)$input['interest_ID'];
        $date_interest = $input['date_interest'];            // string
        $new_val_interest = (float)$input['new_val_interest']; // float
        $update_PMT = (int)$input['update_PMT'];

        // Prepare SQL update
        $query = "
            UPDATE interest_repayments
               SET date_interest = ?,
                   new_val_interest = ?,
                   update_PMT = ?
             WHERE ID_user = ? 
               AND DB_set  = ?
               AND interest_ID = ?
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
            $date_interest,    // s = string
            $new_val_interest, // d = double
            $update_PMT,                 // i = integer
            $ID_user,                    // i = integer
            $DB_set,                     // i = integer
            $interest_ID                  // i = integer
        );

        if (!$stmt->execute()) {
            echo json_encode([
                "success" => false,
                "message" => "Failed to execute query: " . $stmt->error
            ]);
            exit;
        }

        if ($stmt->affected_rows > 0) {
            echo json_encode([
                "success" => true,
                "message" => "Interest updated successfully"
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "No changes made or interest not found"
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
        $interest_ID = isset($input['interest_ID']) ? (int)$input['interest_ID'] : 0;
        $DB_set = isset($input['DB_set']) ? (int)$input['DB_set'] : 0;

        if ($interest_ID && $DB_set) {
            $query = "DELETE FROM interest_repayments WHERE ID_user = ? AND DB_set = ? AND interest_ID = ?";
            $stmt = $db->prepare($query);
            $stmt->bind_param("iii", $ID_user, $DB_set, $interest_ID);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                echo json_encode(["success" => true, "message" => "Interest deleted successfully"]);
            } else {
                echo json_encode(["success" => false, "message" => "Interest not found or failed to delete"]);
            }

            $stmt->close();
        } else {
            echo json_encode(["success" => false, "message" => "Missing DB_set or interest_ID"]);
        }
    exit;

    break;

    default:
        echo json_encode(["success" => false, "message" => "Invalid request method"]);
        break;
}
?>
