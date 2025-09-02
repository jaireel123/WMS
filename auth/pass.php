<?php
$plainPassword = "123456";
$hash = password_hash($plainPassword, PASSWORD_BCRYPT);
echo $hash;
