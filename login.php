<?php
session_start();
global $conn;

include 'elementeWebseite/database_connection.php';

// Handle login before any HTML/output
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

        // Redirect to the admin page
        header("Location: admin_ansicht.php");
        exit();
    } else {
        $_SESSION['error'] = "Invalid username or password.";

        // Redirect back to the login page
        header("Location: login.php");
        exit();
    }
}

include "elementeWebseite/header.php";
?>

<div class="container px-4 py-5">
    <h1 class="pb-2 border-bottom">Login</h1>
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
            <form method="POST" action="login.php">
                <div class="mb-3">
                    <label for="username" class="form-label">Nome utente</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary">Accedi</button>
            </form>
        </div>
        <div class="col-md-8">
            <img src="images/book.jpg" alt="Libro antico" class="img-fluid rounded shadow-lg" style="max-height: 400px; object-fit: cover;">
        </div>
    </div>
</div>

<?php include 'elementeWebseite/footer.php'; ?>