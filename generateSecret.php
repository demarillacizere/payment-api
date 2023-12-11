<?php
$jwtSecretKey = bin2hex(random_bytes(32));
echo $jwtSecretKey.PHP_EOL;