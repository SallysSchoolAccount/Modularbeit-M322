<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}
global $conn;
include './elementeWebseite/header.php';
include './elementeWebseite/database_connection.php';

// Adding a new customer
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $vorname = $_POST['vorname'];
    $name = $_POST['name'];
    $geburtstag = $_POST['geburtstag'];
    $geschlecht = $_POST['geschlecht'];
    $email = $_POST['email'];
    $kontaktpermail = $_POST['kontaktpermail'];

    // Query to insert a new customer
    $query = "INSERT INTO kunden (vorname, name, geburtstag, geschlecht, email, kontaktpermail) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssisss", $vorname, $name, $geburtstag, $geschlecht, $email, $kontaktpermail);

    if ($stmt->execute()) {
        header("Location: bucherVeraendern_table.php");
        exit();
    }
}
?>

<div class="container">
    <h2 class="pb-2 border-bottom">Neuen Kunden hinzufügen</h2>
    <form method="POST" class="col-md-6">
        <!-- Vorname hinzufügen -->
        <div class="mb-3">
            <label class="form-label">Vorname</label>
            <input type="text" name="vorname" class="form-control" required>
        </div>

        <!-- Name hinzufügen -->
        <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <!-- Geburtsdatum hinzufügen -->
        <div class="mb-3">
            <label class="form-label">Geburtsdatum</label>
            <input type="date" name="geburtstag" class="form-control" required>
        </div>

        <!-- Geschlecht hinzufügen -->
        <div class="mb-3">
            <label class="form-label">Geschlecht</label><br>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="geschlecht" id="geschlechtM" value="M" required>
                <label class="form-check-label" for="geschlechtM">Männlich</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="geschlecht" id="geschlechtW" value="W">
                <label class="form-check-label" for="geschlechtW">Weiblich</label>
            </div>
        </div>

        <!-- Email hinzufügen -->
        <div class="mb-3">
            <label class="form-label">E-mail</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <!-- Kontakt per Mail hinzufügen -->
        <div class="mb-3">
            <label class="form-label">Kontakt per Mail</label>
            <select name="kontaktpermail" class="form-select" required>
                <option value="Ja">Ja</option>
                <option value="Nein">Nein</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Kunden hinzufügen</button>
    </form>
</div>