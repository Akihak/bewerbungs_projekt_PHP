<?php
require_once "pdo.php";
require_once "util.php";
require_once "head.php";
session_start();
if ( isset($_POST['logout']) ) {
    header('Location: index.php');
    return;
}
?>
<!DOCTYPE html>
<html>
<style>
table, th, td {
  border:1px solid black;
}
</style>
<head>
<title>Arthur Görzen</title>
</head>
<body>
<div class="container">
<h1>Arthur Görzen's Resume Registry</h1>
<?php

flashMessages();

if (isset($_SESSION['name']) ) {
    $stmt = $pdo->query("SELECT first_name, last_name FROM profile");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if($row !== false) {
        echo("<table>");
            echo "<tr>";
            echo "<th>Name</th>";
            echo "<th>Headline</th>";
            echo "<th>Action</th>";
            echo "</tr>";
        $stmt = $pdo->query("SELECT first_name, last_name, headline, profile_id FROM profile");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo("<tr><td>");
            echo('<a href="view.php?profile_id='.$row['profile_id'].'">'.htmlentities($row['first_name']." ".$row['last_name']).'</a>');
            echo("</td><td>");
            echo(htmlentities($row['headline']));
            echo("</td><td>");
            echo('<a href="edit.php?profile_id='.$row['profile_id'].'">Edit</a> ');
            echo('<a href="delete.php?profile_id='.$row['profile_id'].'">Delete</a>');
            echo("</td></tr>\n");
        }
        echo("</table>");
        
    }
        echo('<p><a href="add.php">Add New Entry</a></p>');
        echo('<a href="logout.php">Logout</a></p>');

    } else {
    $stmt = $pdo->query("SELECT first_name, last_name FROM profile");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if($row !== false) {
        echo("<table>");
            echo "<tr>";
            echo "<th>Name</th>";
            echo "<th>Headline</th>";
            echo "</tr>";
        $stmt = $pdo->query("SELECT first_name, last_name, headline, profile_id FROM profile");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo("<tr><td>");
            echo('<a href="view.php?profile_id='.$row['profile_id'].'">'.htmlentities($row['first_name']." ".$row['last_name']).'</a>');
            echo("</td><td>");
            echo(htmlentities($row['headline']));
            echo("</td></tr>\n");
        }
        echo("</table>");
    }
    echo('<p><a href="login.php">Please log in</a></p>');
}
?>
</div>
</body>
</html>