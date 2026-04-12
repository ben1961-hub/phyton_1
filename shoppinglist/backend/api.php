<?php
header('Content-Type: application/json');

// Cookie für 30 Tage gültig
$lifetime = 30 * 24 * 60 * 60; // 30 Tage in Sekunden

// Server-seitige Session-Lifetime auf 30 Tage setzen
ini_set('session.gc_maxlifetime', $lifetime);

session_set_cookie_params([
    'lifetime' => $lifetime,   // Dauer
    'path' => '/',              // überall gültig
    'domain' => '',             // leer = aktuelle Domain
    'secure' => false,          // true wenn HTTPS
    'httponly' => true,         // JS kann Cookie nicht lesen
    'samesite' => 'Lax'         // Cross-Site Sicherheit
]);

session_start();
$data = json_decode(file_get_contents("php://input"), true);
$action = $_GET['action'] ?? '';

if($action === "login"){
    $user = $data['user'];
    $pass = $data['pass'];

    // 🔒 HARDCODED LOGIN (einfachste Variante)
    if( (strtolower($user) === "alrun" && $pass === "welzow") || (strtolower($user) === "bernhard" && $pass === "welzow") ){
        $_SESSION['login'] = true;
        echo json_encode(["success"=>true]);
    } else {
        echo json_encode(["success"=>false]);
    }
    exit;
}

if($action === "logout"){
    session_destroy();
    exit;
}

if($action === "check"){
    echo json_encode([
        "loggedIn" => !empty($_SESSION['login'])
    ]);
    exit;
}

if(!isset($_SESSION['login'])){
    echo json_encode([]);
    exit;
}

// Verbindung zur Datenbank
require_once "db.php";

// Action aus der URL lesen
// Beispiel: api.php?action=load
// ?? "" bedeutet: Wenn action nicht existiert, dann verwende einen leeren String.
$action = $_GET["action"] ?? "";

// JSON-Input lesen
// json_decode(..., true) wandelt JSON in ein PHP-Array um:
$input = json_decode(file_get_contents("php://input"), true);

// Beispiel Request:
// {
//		"text":"Milch kaufen",
// 		"list":"shopping",
//		"pos":1
// }

switch($action){
	case "load":
		$stmt = $pdo->query("SELECT * FROM items ORDER BY list,pos");
		echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
		break;

	case "add":
		$stmt=$pdo->prepare("INSERT INTO items (list,text,done,pos) VALUES (?,?,0,?)");
		$stmt->execute([
		$input["list"],
		$input["text"],
		$input["pos"]
		]);

		echo $pdo->lastInsertId();
	break;


	case "update":
		$stmt=$pdo->prepare("UPDATE items SET text=?,done=?,list=?,pos=? WHERE id=?");
		$stmt->execute([
		$input["text"],
		$input["done"],
		$input["list"],
		$input["pos"],
		$input["id"]
		]);
	break;


	case "delete":
		$stmt=$pdo->prepare("DELETE FROM items WHERE id=?");
		$stmt->execute([$input["id"]]);
	break;
}
?>