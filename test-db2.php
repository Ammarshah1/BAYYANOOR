<?php $mysqli = new mysqli('127.0.0.1', 'root', ''); if ($mysqli->connect_errno) { echo 'Failed to connect: ' . $mysqli->connect_error; } else { echo 'MySQL connection successful!'; } ?>
