<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

header('Access-Control-Allow-Origin: *');

header('Access-Control-Allow-Methods: GET, POST');

header("Access-Control-Allow-Headers: X-Requested-With");

// Response Reusable Function
function respond($code, $response)
{
    header("Content-Type:application/json");
    http_response_code($code);
    echo (is_array($response) ? json_encode($response) : $response);
    exit(0);
}

// Establish Connection with DataBase
function getDb()
{
    $con = mysqli_connect("localhost", "mavenjan21", "genius4good", "ccl_learning");
    if (mysqli_connect_errno()) {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
        exit(0);
    }
    return $con;
}

// Get current domain name the api is being served from
function getUrl()
{
    $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $parts = parse_url($actual_link);
    $scheme = explode('/', $parts['scheme']);
    $host = explode('/', $parts['host']);
    $hostUrl = $scheme[0] . "://" . $host[0];
    return $hostUrl;
}

if ($json = json_decode(file_get_contents("php://input"), true))
    $request = $json;
elseif ($_POST)
    $request = $_POST;
elseif ($_GET)
    $request = $_GET;
$log = strftime('%Y-%m-%d');
$time = strftime('%H:%M:%S');

try {

    // Establish DB Connection
    $db = getDb();

    if (stripos($_SERVER['REQUEST_URI'], '/testEndpoint') !== false) {
        respond(200, array('success' => true, 'message' => 'Endpoint Reachable'));
    } elseif (stripos($_SERVER['REQUEST_URI'], '/signup') !== false) {
        $companyName = $request['companyName'];
        $companyAddress = $request['companyAddress'];
        $companyTel = $request['companyTel'];
        $firstName = $request['firstName'];
        $lastName = $request['lastName'];
        $address = $request['address'];
        $email = $request['email'];
        $tel = $request['tel'];
        $hash = password_hash($request['password'], PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (userFirstName, userLastName, userEmail, userPhone, userPassword, userRoleID) VALUES ('$firstName', '$lastName', '$email', '$tel', '$hash', 1)";
        $db->query($sql);
        if ($db->errno)
            respond(500, ["success" => false, "message" => 'db error: ' . $db->error]);
        $userID = $db->insert_id;

        $sql = "INSERT INTO company (companyName, companyAddress1, companyAdminID) VALUES ('$companyName', '$companyAddress', '$userID')";
        $db->query($sql);
        if ($db->errno)
            respond(500, ["success" => false, "message" => 'db error: ' . $db->error]);

        respond(200, ["success" => true, "message" => "Sign Up Successful"]);
    } elseif (stripos($_SERVER['REQUEST_URI'], '/login') !== false) {
        $email = $request["email"];
        $password = $request["password"];

        $sql = "SELECT * FROM users JOIN role ON users.userRoleID = role.roleID
                LEFT JOIN company ON users.userID = company.companyAdminID WHERE users.userEmail = '$email'";
        $result = $db->query($sql);
        if ($db->errno)
            respond(500, ["success" => false, "message" => 'db error: ' . $db->error]);
        if ($result->num_rows === 1) {
            $userDetails = $result->fetch_array(MYSQLI_ASSOC);
            $pass_ok = password_verify($password, $userDetails['userPassword']);
            if ($pass_ok) {
                $userData = ["firstname" => $userDetails["userFirstName"], "lastname" => $userDetails["userLastName"], "email" => $userDetails["userEmail"], "role" => $userDetails["roleName"], "companyName" => $userDetails["companyName"]];
                respond(200, ["success" => true, "message" => "Login Successful", "userData" => $userData]);
            } else
                respond(400, ['success' => false, 'error' => 'login failed, invalid email or password']);
        } else {
            respond(400, ['success' => false, 'error' => 'User not registered']);
        }
    } else {
        respond(404, array('success' => false, 'error' => 'resource or endpoint not found'));
    }
} catch (Exception $e) {
    try {
        $entry = ['time' => $time, 'request' => $request, 'error' => json_encode($e)];
        $fp = file_put_contents('logs/' . $log . '.txt', json_encode($entry, JSON_PRETTY_PRINT), FILE_APPEND);
        respond(500, array('success' => false, 'error' => $e->getMessage()));
    } catch (Exception $ex) {
        respond(500, array('success' => false, 'error' => $e->getMessage() . '|' . $ex->getMessage()));
    }
} finally {
    if ($db)
        $db->close();
}
