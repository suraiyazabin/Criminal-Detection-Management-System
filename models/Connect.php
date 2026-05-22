<?php

function connect()
{
    $conn = mysqli_connect("localhost", "root", "", "cdms");
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    return $conn;
}