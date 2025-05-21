<?php
session_start();
global $conn;

include 'elementeWebseite/database_connection.php';

// Registrierung überprüfen und verarbeiten
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $benutzername = $_POST['username'];
    $passwort = $_POST['password'];
    $bestaetigung_passwort = $_POST['confirm_password'];

    // Überprüfen, ob die Passwörter übereinstimmen
    if ($passwort !== $bestaetigung_passwort) {
        $_SESSION['error'] = "Die Passwörter stimmen nicht überein.";
        header("Location: register.php");
        exit();
    }

    // Passwort verschlüsseln
    $verschluesseltes_passwort = password_hash($passwort, PASSWORD_BCRYPT);

    // Neuen Administrator-Benutzer in die Datenbank einfügen
    $query = "INSERT INTO benutzer (benutzername, passwort, admin) VALUES (?, ?, 1)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $benutzername, $verschluesseltes_passwort);

    if ($stmt->execute()) {
        // Erfolgsmeldung setzen und zur Login-Seite weiterleiten
        $_SESSION['success'] = "Das Konto wurde erfolgreich erstellt. Bitte melden Sie sich an.";
        header("Location: login.php");
        exit();
    } else {
        // Fehler beim Erstellen des Kontos
        $_SESSION['error'] = "Fehler beim Erstellen des Kontos. Bitte versuchen Sie es erneut.";
        header("Location: register.php");
        exit();
    }
}

include "elementeWebseite/header.php";
?>

    <div class="container px-4 py-5">
        <h1 class="pb-2 border-bottom">Registrieren</h1>
        <div class="row">
            <div class="col-md-4">
                <?php if (isset($_SESSION['error'])) : ?>
                    <div class="alert alert-danger" role="alert">
                        <?php
                        echo $_SESSION['error'];
                        unset($_SESSION['error']);
                        ?>
                    </div>
                <?php endif; ?>
                <form method="POST" action="register.php">
                    <div class="mb-3">
                        <label for="username" class="form-label">Benutzername</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Passwort</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Passwort bestätigen</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Registrieren</button>
                </form>
            </div>
            <div class="col-md-8">
                <img src="images/register.jpg" alt="Bild zur Registrierung" class="img-fluid rounded shadow-lg" style="max-height: 400px; object-fit: cover;">
            </div>
        </div>
    </div>

<?php include 'elementeWebseite/footer.php'; ?>