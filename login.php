<?php
session_start();
global $conn;

include 'elementeWebseite/database_connection.php';

// Login überprüfen und verarbeiten
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $benutzername = $_POST['username'];
    $passwort = $_POST['password'];

    $query = "SELECT * FROM benutzer WHERE benutzername = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $benutzername);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();

    if ($admin && password_verify($passwort, $admin['passwort'])) {
        $_SESSION['admin_logged_in'] = true;

        // Weiterleitung zur Admin-Seite
        header("Location: admin_ansicht.php");
        exit();
    } else {
        $_SESSION['error'] = "Ungültiger Benutzername oder Passwort.";

        // Zurück zur Login-Seite weiterleiten
        header("Location: login.php");
        exit();
    }
}

include "elementeWebseite/header.php";
?>

    <div class="container px-4 py-5">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">

                <!-- Card Container for Form -->
                <div class="card shadow-lg border-0 rounded-3">
                    <div class="card-body">
                        <h2 class="text-center mb-4">Anmelden</h2>
                        <!-- Fehleranzeige -->
                        <?php if (isset($_SESSION['error'])) : ?>
                            <div class="alert alert-danger" role="alert">
                                <?php
                                echo $_SESSION['error'];
                                unset($_SESSION['error']);
                                ?>
                            </div>
                        <?php endif; ?>

                        <!-- Login-Formular -->
                        <form method="POST" action="login.php">
                            <div class="mb-3">
                                <label for="username" class="form-label">Benutzername</label>
                                <input type="text" class="form-control" id="username" name="username" placeholder="Geben Sie Ihren Benutzernamen ein" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Passwort</label>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Geben Sie Ihr Passwort ein" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">Anmelden</button>
                            </div>
                        </form>
                    </div>
                    <!-- Footer Links -->
                    <div class="card-footer text-center py-3">
                        <p class="mb-0">Noch kein Konto? <a href="register.php" class="text-decoration-none">Registrieren</a></p>
                    </div>
                </div>
            </div>
        </div>


<?php include 'elementeWebseite/footer.php'; ?>