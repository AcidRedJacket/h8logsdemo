<?php
// =========================================================
// === 0. CRITICAL DEBUGGING LINES (MUST BE FIRST) ===
// These lines force PHP to display errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// =========================================================

// --- 1. Database Configuration (Run connection check early) ---
$servername = "localhost";
$username = "root";
// SECURITY BEST PRACTICE: DO NOT HARDCODE CREDENTIALS IN THIS FILE.
// Move these variables to a non-web-accessible configuration file (e.g., ../config.php).
$password = "Panther46"; 
$dbname = "package";

// Connect to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// === CRITICAL DEBUGGING CHECK: FORCED PLAIN-TEXT ERROR ON CONNECTION FAILURE ===
if ($conn->connect_error) {
    http_response_code(500);
    header("Content-Type: text/plain; charset=UTF-8"); 
    die("FATAL API ERROR (PRE-SESSION CRASH): Database Connection Failed.\n\n" . 
        "1. Check that your MySQL/MariaDB server is actively RUNNING.\n" .
        "2. Ensure the 'mysqli' PHP extension is ENABLED in php.ini.\n" .
        "Database Error Message: " . $conn->connect_error);
}
// === END CRITICAL DEBUGGING CHECK ===

// Now start the session and set headers, only if the DB connection succeeded
session_start();

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE"); 
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");


// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}


// Get the request method and incoming JSON data
$method = $_SERVER['REQUEST_METHOD'];
$data = [];
if ($method !== 'GET') {
    $data = json_decode(file_get_contents("php://input"), true);
}

$action = $_GET['action'] ?? ''; // Action for authentication

// Helper function to handle response for invalid input or errors
function respond_error($conn, $message, $code = 422) {
    http_response_code($code);
    echo json_encode(["message" => $message]);
    // Close the connection before exit
    $conn->close();
    exit;
}

// Helper function for Admin authentication check
function check_admin_auth($conn) {
    if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
        respond_error($conn, "Access Denied. Only Admin users can perform this action.", 403);
    }
}

// --- 2. Authentication Actions (Login/Register/Logout) ---

if ($action === 'register' && $method === 'POST') {
    // REGISTRATION CODE VALIDATION IS NOW HANDLED BY QUERYING THE 'registration_codes' DATABASE TABLE
    
    $user = trim($data['username'] ?? '');
    $pass = trim($data['password'] ?? '');
    $reg_code = trim($data['registrationCode'] ?? ''); 

    if (empty($user) || empty($pass) || strlen($user) < 3 || strlen($pass) < 6) {
        respond_error($conn, "Username and password are required (min 3/6 chars).", 400);
    }
    
    // --- Database check for Registration Code ---
    // Check if the provided code exists and is active (is_active = 1)
    $stmt_code = $conn->prepare("SELECT code FROM registration_codes WHERE code = ? AND is_active = 1");
    if (!$stmt_code) {
        respond_error($conn, "Database error during code check preparation: " . $conn->error, 500);
    }
    $stmt_code->bind_param("s", $reg_code);
    $stmt_code->execute();
    $result_code = $stmt_code->get_result();
    $stmt_code->close();

    if ($result_code->num_rows === 0) {
        respond_error($conn, "Invalid or inactive registration code.", 403); 
    }
    
    $hashed_password = password_hash($pass, PASSWORD_DEFAULT);
    
    // PATCH: Using prepared statements to prevent SQL Injection
    $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'user')");
    if (!$stmt) {
        respond_error($conn, "Database error during registration preparation: " . $conn->error, 500);
    }
    
    $stmt->bind_param("ss", $user, $hashed_password);

    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode(["message" => "User registered successfully"]);
    } else {
        // Check for duplicate key error (errno 1062)
        if ($stmt->errno == 1062) {
            respond_error($conn, "Username already exists.", 409);
        } else {
            respond_error($conn, "Registration error: " . $stmt->error, 500);
        }
    }
    $stmt->close();

} elseif ($action === 'login' && $method === 'POST') {
    $user = trim($data['username'] ?? '');
    $pass = trim($data['password'] ?? '');

    // PATCH: Using prepared statements to prevent SQL Injection
    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
    if (!$stmt) {
        respond_error($conn, "Database error during login preparation: " . $conn->error, 500);
    }
    
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if ($result && $result->num_rows === 1) {
        $row = $result->fetch_assoc();
        
        if (password_verify($pass, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];
            
            http_response_code(200);
            echo json_encode(["message" => "Login successful", "username" => $row['username'], "role" => $row['role'], "userId" => $row['id']]);
        } else {
            respond_error($conn, "Invalid credentials.", 401);
        }
    } else {
        respond_error($conn, "Invalid credentials.", 401);
    }

} elseif ($action === 'logout' && $method === 'POST') {
    session_unset();
    session_destroy();
    http_response_code(200);
    echo json_encode(["message" => "Logged out successfully"]);

// --- User/Role Management Actions (Admin Only) ---
} elseif ($action === 'users' && $method === 'GET') {
    check_admin_auth($conn);
    
    // SAFE: No user input
    $sql = "SELECT id, username, role FROM users ORDER BY username ASC";
    $result = $conn->query($sql);

    $users = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            // Do not send password hashes
            $users[] = $row;
        }
    }
    http_response_code(200);
    echo json_encode($users);

} elseif ($action === 'updateRole' && $method === 'POST') {
    check_admin_auth($conn);

    $user_id = $data['userId'] ?? null;
    $new_role = $data['newRole'] ?? null;

    if (empty($user_id) || empty($new_role) || !in_array($new_role, ['admin', 'user'])) {
        respond_error($conn, "Invalid user ID or role.", 400);
    }

    // Prevent an admin from demoting themselves!
    if ($_SESSION['user_id'] == $user_id && $new_role === 'user') {
         respond_error($conn, "Cannot demote your own account.", 403);
    }

    // PATCH: Using prepared statements to prevent SQL Injection
    // Assuming user_id is an integer (i)
    $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
    if (!$stmt) {
        respond_error($conn, "Database error during role update preparation: " . $conn->error, 500);
    }
    
    $stmt->bind_param("si", $new_role, $user_id); 

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            http_response_code(200);
            echo json_encode(["message" => "User role updated successfully"]);
        } else {
            respond_error($conn, "User not found or role already set.", 404);
        }
    } else {
        respond_error($conn, "Error updating role: " . $stmt->error, 500);
    }
    $stmt->close();

// --- NEW: Registration Code Management Actions (Admin Only) ---

} elseif ($action === 'getCodes' && $method === 'GET') {
    check_admin_auth($conn);
    
    // SAFE: No user input
    $sql = "SELECT code, is_active, created_at FROM registration_codes ORDER BY created_at DESC";
    $result = $conn->query($sql);

    $codes = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $codes[] = $row;
        }
    }
    http_response_code(200);
    echo json_encode($codes);

} elseif ($action === 'addCode' && $method === 'POST') {
    check_admin_auth($conn);

    $code = trim($data['code'] ?? '');
    
    if (empty($code) || strlen($code) < 3) {
        respond_error($conn, "Invalid code format. Must be at least 3 characters.", 400);
    }

    // PATCH: Using prepared statements to prevent SQL Injection
    $stmt = $conn->prepare("INSERT INTO registration_codes (code, is_active) VALUES (?, 1)");
    if (!$stmt) {
        respond_error($conn, "Database error during code insertion preparation: " . $conn->error, 500);
    }
    
    $stmt->bind_param("s", $code);

    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode(["message" => "Registration code added successfully"]);
    } else {
        if ($stmt->errno == 1062) { // Duplicate key error
            respond_error($conn, "Code already exists.", 409);
        } else {
            respond_error($conn, "Error adding code: " . $stmt->error, 500);
        }
    }
    $stmt->close();

} elseif ($action === 'toggleCodeStatus' && $method === 'POST') {
    check_admin_auth($conn);

    $code = trim($data['code'] ?? '');
    $new_status = (int)($data['newStatus'] ?? 0); // Expects 0 (inactive) or 1 (active)

    if (empty($code) || !in_array($new_status, [0, 1])) {
        respond_error($conn, "Invalid code or status value.", 400);
    }

    // PATCH: Using prepared statements for UPDATE query
    $stmt = $conn->prepare("UPDATE registration_codes SET is_active = ? WHERE code = ?");
    if (!$stmt) {
        respond_error($conn, "Database error during code status update preparation: " . $conn->error, 500);
    }
    
    // Bind parameters: integer (i) for status, string (s) for code
    $stmt->bind_param("is", $new_status, $code); 

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            http_response_code(200);
            echo json_encode(["message" => "Code status updated successfully"]);
        } else {
            respond_error($conn, "Code not found.", 404);
        }
    } else {
        respond_error($conn, "Error updating code status: " . $stmt->error, 500);
    }
    $stmt->close();

// --- 3. Package CRUD Operations ---

} elseif ($method == 'GET') {
    // SAFE: No user input
    $sql = "SELECT * FROM packages ORDER BY id DESC";
    $result = $conn->query($sql);

    $packages = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $packages[] = $row;
        }
    }
    echo json_encode($packages);
}

elseif ($method == 'POST') {
    // Auth Check
    if (!isset($_SESSION['user_id'])) {
        respond_error($conn, "Authentication required to log or edit a package. Please log in.", 401);
    }
    
    if (empty($data['personName']) || empty($data['itemName'])) {
        respond_error($conn, "Recipient Name and Item Name are required.", 400);
    }
    
    // Extract and type-check/validate all raw input variables
    $personName = $data['personName'];
    $itemName = $data['itemName'];
    $quantity = (int)($data['quantity'] ?? 1);
    $weight = number_format((float)($data['weight'] ?? 0.0), 2, '.', '');
    $tracking = $data['tracking'] ?? '';
    $poNumber = $data['poNumber'] ?? '';
    $location = $data['location'] ?? '';
    $isTally = $data['isTally'] ?? 'No';
    $isDamaged = $data['isDamaged'] ?? 'No';
    
    // Check if we are updating an existing package (EDIT)
    if (isset($data['id_to_edit'])) {
        if ($_SESSION['role'] !== 'admin') {
            respond_error($conn, "Only Admins can edit existing package logs.", 403);
        }
        
        $id = $data['id_to_edit']; 
        $loggedBy = $data['loggedBy'] ?? $_SESSION['username'];

        // PATCH: Using prepared statements for UPDATE query
        $sql = "UPDATE packages SET 
                personName = ?, loggedBy = ?, itemName = ?, quantity = ?, weight = ?, 
                tracking = ?, poNumber = ?, location = ?, isTally = ?, isDamaged = ? 
                WHERE id = ?";
                
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            respond_error($conn, "Database error during package update preparation: " . $conn->error, 500);
        }
        
        // Bind parameters: sssidssssss (11 parameters)
        $stmt->bind_param("sssidssssss", 
            $personName, $loggedBy, $itemName, $quantity, $weight, 
            $tracking, $poNumber, $location, $isTally, $isDamaged, 
            $id);
            
        if ($stmt->execute()) {
            http_response_code(200);
            echo json_encode(["message" => "Package updated successfully"]);
        } else {
            respond_error($conn, "Error updating record: " . $stmt->error, 500);
        }
        $stmt->close();
        
    } else {
        // CREATE new package
        $id = $data['id'] ?? time() * 1000; 
        $loggedBy = $_SESSION['username']; // Logged by current session user
        
        // PATCH: Using prepared statements for INSERT query
        $sql = "INSERT INTO packages (id, personName, loggedBy, itemName, quantity, weight, tracking, poNumber, location, isTally, isDamaged)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            respond_error($conn, "Database error during package insertion preparation: " . $conn->error, 500);
        }
        
        // Bind parameters: sssidssssss (11 parameters)
        $stmt->bind_param("sssidssssss", 
            $id, $personName, $loggedBy, $itemName, $quantity, $weight, 
            $tracking, $poNumber, $location, $isTally, $isDamaged);

        if ($stmt->execute()) {
            http_response_code(201);
            echo json_encode(["message" => "New package logged successfully", "id" => $id]);
        } else {
            respond_error($conn, "Error inserting record: " . $stmt->error, 500);
        }
        $stmt->close();
    }
}

elseif ($method == 'DELETE') {
    if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
        respond_error($conn, "Access Denied. Only Admin users can delete package logs.", 403);
    }
    
    $id = $data['id'] ?? null;
    
    if (empty($id)) {
        respond_error($conn, "Missing package ID for deletion.", 400);
    }
    
    // PATCH: Using prepared statements to prevent SQL Injection
    $stmt = $conn->prepare("DELETE FROM packages WHERE id = ?");
    if (!$stmt) {
        respond_error($conn, "Database error during deletion preparation: " . $conn->error, 500);
    }
    
    $stmt->bind_param("s", $id); // Binding ID as string

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            http_response_code(200);
            echo json_encode(["message" => "Package deleted successfully"]);
        } else {
            respond_error($conn, "Package not found.", 404);
        }
    } else {
        respond_error($conn, "Error deleting record: " . $stmt->error, 500);
    }
    $stmt->close();
}

else {
    http_response_code(405);
    echo json_encode(["message" => "Method not allowed"]);
}

// Final connection close
$conn->close();
?>
