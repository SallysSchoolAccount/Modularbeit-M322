<?php
//Head & Header
include 'elementeWebseite/header.php';
//Verbindung Datenbank
include 'elementeWebseite/database_connection.php';

if (isset($_GET['id'])) {
    //ID nehmen
    $book_id = $_GET['id'];

    //MySQL Befehl
    $query = "SELECT b.*, z.beschreibung, k.kategorie FROM buecher b, zustaende z, kategorien k WHERE b.id = ? and b.kategorie = k.id and b.zustand = z.zustand;";


    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $book = $result->fetch_assoc();
} else {
    echo "Kein Buch vorhanden.";
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
                <h4><strong>Autor: </strong> <?php echo htmlspecialchars($book['autor']); ?></h4>
                <h5><strong>ID: </strong><?php echo htmlspecialchars($book['id'])?></h5>
                <h5><strong>Kategorie: </strong> <?php echo htmlspecialchars($book['kategorie']); ?></h5>
                <h5><strong>Zustand: </strong><?php echo htmlspecialchars($book['beschreibung']);?></h5><br>

                <p><strong>Beschreibung: </strong> <?php echo htmlspecialchars($book['title']); ?></p>
            </div>
        </div>
    </div>

<!--Footer-->
<?php include 'elementeWebseite/footer.php'; ?>