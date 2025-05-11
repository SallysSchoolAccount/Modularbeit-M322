<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}
global $conn;
include 'elementeWebseite/database_connection.php';

// Recupera i dati dell'utente dal database
$stmt = $conn->prepare("SELECT benutzer.name, benutzer.vorname FROM benutzer WHERE benutzername = ?");
$stmt->bind_param("s", $_SESSION['benutzername']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$name = $user['name'] ?? 'Admin';
$vorname = $user['vorname'] ?? '';

include "elementeWebseite/header.php";
?>


<div class="container mt-5">
        <div class="row">
            <!-- Colonna per il nome e cognome -->
            <div class="col-md-3">
                <div class="card shadow-lg">
                    <div class="card-body">
                        <h5>Benvenuto,</h5>
                        <p><strong><?php echo htmlspecialchars($name . " " . $vorname); ?></strong></p>
                    </div>
                </div>
            </div>

            <!-- Colonna per la dashboard -->
            <div class="col-md-6">
                <div class="card shadow-lg">
                    <div class="card-header text-center bg-primary text-white">
                        <h2>Admin Dashboard</h2>
                    </div>
                    <div class="card-body">
                        <p class="text-center">Benvenuto nella dashboard amministrativa. Sei loggato come admin.</p>
                        <div class="d-flex justify-content-center mt-3">
                            <a href="bucherVeraendern_table.php" class="btn btn-primary me-2">Modifica Libri</a>
                            <a href="edit_employees.php" class="btn btn-secondary">Modifica Dipendenti</a>
                            <a href="login.php" class="btn btn-secondary">Logout</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include "elementeWebseite/footer.php"; ?>