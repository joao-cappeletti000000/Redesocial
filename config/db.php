<?php

$conn = new PDO("mysql:host=localhost;dbname=conecta_escola","root","");

$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

?>