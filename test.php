<?php

$r1 = fopen('php://temp', 'r+');
$r2 = fopen('php://temp', 'r+');

var_dump(stream_get_meta_data($r1));