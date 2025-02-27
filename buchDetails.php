<?php
//Head & Header
include 'elementeWebseite/header.php';
//Verbindung Webseite
include 'elementeWebseite/database_connection.php';

if (isset($_GET['id'])) {
    //Damit das ID in das URL angezeigt wird
    $book_id = $_GET['id'];

    //MySQL Befehl
    $query = "SELECT * FROM buecher WHERE id = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $book = $result->fetch_assoc();
} else {
    echo "No book ID provided.";
    exit;
}
?>

    <div class="container">
        <h2 class="pb-2 border-bottom"><?php echo htmlspecialchars($book['kurztitle']); ?></h2>
        <div class="row">
            <div class="col-md-4">
                <img src="images/book.jpg" alt="Book Cover" class="img-fluid">
            </div>
            <div class="col-md-8">
                <h4><strong>Autor:</strong> <?php echo htmlspecialchars($book['autor']); ?></h4><br>
                <p><strong>Beschreibung:</strong> <?php echo htmlspecialchars($book['title']); ?></p>
            </div>
        </div>
    </div>

<!--Footer-->
<?php include 'elementeWebseite/footer.php'; ?>