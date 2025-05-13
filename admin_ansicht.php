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
        <div class="row g-4">
            <!-- Colonna per il nome e cognome -->
            <div class="col-md-3">
                <div class="card shadow-lg bg-dark text-light">
                    <div class="card-body text-center">
                        <h5 class="text-uppercase text-warning">Willkommen,</h5>
                        <p class="fw-bold"><?php echo htmlspecialchars($name . " " . $vorname); ?></p>
                    </div>
                </div>
            </div>

            <!-- Colonna per la dashboard -->
            <div class="col-md-6">
                <div class="card shadow-lg">
                    <div class="card-header text-center bg-warning text-dark">
                        <h2 class="text-uppercase">Admin-Dashboard</h2>
                    </div>
                    <div class="card-body bg-dark text-light">
                        <p class="text-center">Willkommen im administrativen Dashboard. Sie sind als Administrator angemeldet.</p>
                        <div class="d-flex justify-content-center mt-3 gap-2">
                            <a href="bucherVeraendern_table.php" class="btn btn-outline-warning">Bücher bearbeiten</a>
                            <a href="kundenSuchen_table.php" class="btn btn-outline-light">Kunden bearbeiten</a>
                            <a href="login.php" class="btn btn-outline-danger">Abmelden</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include "elementeWebseite/footer.php"; ?>