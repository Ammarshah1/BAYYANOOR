<?php $mysqli = new mysqli('localhost', 'root', ''); if ($mysqli->connect_errno) { echo 'Failed to connect: ' . $mysqli->connect_error; } else { echo 'MySQL connection successful!'; } ?>
