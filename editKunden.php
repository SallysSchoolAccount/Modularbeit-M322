<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}
global $conn;
include './elementeWebseite/header.php';
include './elementeWebseite/database_connection.php';

$kid = isset($_GET['kid']) ? (int)$_GET['kid'] : 0;

//Veränderung Funktion
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $vorname = $_POST['vorname'];
    $name = $_POST['name'];
    $geburtstag = $_POST['geburtstag'];
    $geschlecht = $_POST['geschlecht'];
    $email = $_POST['email'];
    $kontaktpermail = $_POST['kontaktpermail'];

    // Query und prepared statement um Buch zu verändern
    $query = "UPDATE kunden SET 
              vorname = ?, 
              name = ?,
              geburtstag = ?,
              geschlecht = ?, 
              email = ?,
              kontaktpermail = ?
              WHERE kid = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssisssi", $vorname, $name, $geburtstag, $geschlecht, $email, $kontaktpermail, $kid);

    if ($stmt->execute()) {
        header("Location: kundenSuchen_table.php");
        exit();
    }
}

// Retrieve customer data
$query = "SELECT * FROM buecher WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $kid);
$stmt->execute();
$customer = $stmt->get_result()->fetch_assoc();

// Ensure $customer is not null
if (!$customer) {
    $customer = [
        'kid' => '',
        'vorname' => '',
        'name' => '',
        'geburtsdatum' => '',
        'geschlecht' => '',
        'email' => ''
    ];
}
?>

<div class="container">
    <h2 class="pb-2 border-bottom">Kunde bearbeiten</h2>
    <form method="POST" class="col-md-6">
        <div class="mb-3">
            <label class="form-label">ID</label>
            <input type="text" class="form-control" value="<?php echo htmlspecialchars($customer['kid']); ?>" disabled>
        </div>

        <!-- Vorname ändern -->
        <div class="mb-3">
            <label class="form-label">Vorname</label>
            <input type="text" name="vorname" class="form-control" value="<?php echo htmlspecialchars($customer['vorname']); ?>" required>
        </div>

        <!-- Name ändern -->
        <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($customer['name']); ?>" required>
        </div>

        <!-- Geburtsdatum ändern -->
        <div class="mb-3">
            <label class="form-label">Geburtsdatum</label>
            <input type="date" name="geburtsdatum" class="form-control" value="<?php echo htmlspecialchars($customer['geburtsdatum']); ?>" required>
        </div>

        <!-- Geschlecht ändern -->
        <div class="mb-3">
            <label class="form-label">Geschlecht</label><br>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="geschlecht" id="geschlechtM" value="M"
                    <?php echo $customer['geschlecht'] === 'M' ? 'checked' : ''; ?> required>
                <label class="form-check-label" for="geschlechtM">Männlich</label>
            </div>

            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="geschlecht" id="geschlechtW" value="W"
                    <?php echo $customer['geschlecht'] === 'W' ? 'checked' : ''; ?>>
                <label class="form-check-label" for="geschlechtW">Weiblich</label>
            </div>
        </div>

        <!-- Email verändern -->
        <div class="mb-3">
            <label class="form-label">E-mail</label>
            <input type="text" name="email" class="form-control" value="<?php echo htmlspecialchars($customer['email']); ?>" required>
        </div>

        <button type="submit" class="btn btn-primary">Speichern</button>
    </form>
</div>