<?php
include'../includes/connect.php';


if (isset($_SESSION['location'])) {
    echo "Location ID in session: " . $_SESSION['location'];
} else {
    echo "No location saved in session.";
}
