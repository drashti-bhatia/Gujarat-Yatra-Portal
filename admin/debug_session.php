<?php
session_start();
echo "<pre>";
echo "Session ID: " . session_id() . "\n";
echo "Session Status: " . session_status() . "\n";
echo "Session Data: \n";
print_r($_SESSION);
echo "Session Cookie: \n";
print_r($_COOKIE);
echo "</pre>";
?>