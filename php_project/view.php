<?php
require_once "pdo.php";
require_once "head.php";

session_start();

if(! isset($_GET['profile_id'])) {
    $_SESSION['error'] = "Could not load profile";
    header('Location: index.php');
    return;
}

$stmt = $pdo->prepare("SELECT first_name, last_name, profile_id, email, headline, summary FROM profile WHERE profile_id =:xyz");
$stmt->execute(array(":xyz" => $_GET['profile_id']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if($row === false) {
    $_SESSION['error'] ="Could not load profile";
    header('Location: index.php');
    return;
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Arthur Görzen</title>
</head>
<body>
<div class="container">
<h1>Profile information</h1>
<p>First Name: <?php echo $row['first_name']; ?></p>
<p>Last Name: <?php echo $row['last_name']; ?></p>
<p>Email: <?php echo $row['email']; ?></p>
<p>Headline: <br/><?php echo $row['headline']; ?></p>
<p>Summary: <br/><?php echo $row['summary']; ?></p>
<?php
$stmt = $pdo->prepare('SELECT year, name FROM education
JOIN institution ON education.institution_id = institution.institution_id
WHERE profile_id = :prof ORDER BY rank');
$stmt->execute(array( ':prof' => $_GET['profile_id']));
if ($stmt->rowCount() > 0) {
echo"<p>Education: \n ";
echo"<ul>";
while( $row = $stmt->fetch(PDO::FETCH_ASSOC) ) {
    echo "<li>";
    echo $row['year'].": ";
    echo $row['name']." \n ";
    echo "</li>";
}
echo"</ul>";
echo "</p>";
}

$stmt = $pdo->prepare("SELECT profile_id, year, description FROM  position WHERE profile_id =:xyz");
$stmt->execute(array(":xyz" => $_GET['profile_id']));
if ($stmt->rowCount() > 0) {
    echo"<p>Position: \n ";
    echo"<ul>";
    while( $row = $stmt->fetch(PDO::FETCH_ASSOC) ) {
        echo "<li>";
        echo $row['year'].": ";
        echo $row['description']." \n ";
        echo "</li>";
    }
    echo"</ul>";
    echo "</p>";
}
?>
<form><a href="index.php">Done</a></form>
</div>
</body>
</html>