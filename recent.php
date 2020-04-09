<?php

function last_lines($filename, $n, $first_data_row=0) {
    $f = fopen($filename, 'r');
    $st = fstat($f);
    $seek = max(0, $st['size'] - 4096);
    fseek($f, $seek, 0);
    $buf = fread($f, 4096);
    fclose($f);

    $p = strpos($buf, "\n");
    $buf = substr($buf, $p+1);

    $lines = explode("\n", $buf);
    if ($lines[count($lines)-1] == '') {
        $lines = array_slice($lines, 0, count($lines)-1);
    }

    if (count($lines) >= $n+1) {
        return array_slice($lines, count($lines)-$n);
    }
    $lines = array_slice($lines, $first_data_row);
    while (count($lines) < $n) {
        array_unshift($lines, $lines[0]);
    }
    return $lines;
}

function unquote($s) {
    if ($s[0] == '"') {
        $s = substr($s, 1);
    }
    if ($s[strlen($s)-1] == '"') {
        $s = substr($s, 0, strlen($s)-1);
    }
    return $s;
}

function data_set($filename, $n) {
    $f = fopen($filename, 'r');
    $header = explode(", ", trim(fgets($f)));
    fclose($f);

    $lines = last_lines($filename, $n);

    $data = array();
    foreach ($lines as $line) {
        $row = explode(", ", $line);
        $daydata = array();
        for ($i = 1; $i < count($header); $i++) {
            $daydata[unquote($header[$i])] = intval($row[$i]);
        }
        $data[unquote($row[0])] = $daydata;
    }
    return $data;
}


$zarazeni = data_set('zarazeni.csv', 8);
$izlijeceni = data_set('izlijeceni.csv', 8);

$data = array('zarazeni' => $zarazeni, 'izlijeceni' => $izlijeceni);

header("Content-type: application/json");
echo json_encode($data, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK);
echo "\n";


