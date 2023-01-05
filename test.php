<?php
require_once("class.php");

$ct = new CheckUsername(false);

print_r($ct->check("yourusername")); // Replace yourusername with whatever u want
