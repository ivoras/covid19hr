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

function get_summary() {
    $cache_filename = "/tmp/time-series.csv.json";
    $st = @stat($cache_filename);
    if (!$st || time() - $st['mtime'] > 4*3600) {
        $data = array();
        $summary = @file_get_contents("https://raw.githubusercontent.com/viborccom/data/master/covid-19/croatia/time-series.csv");
        if ($summary) {
            $lines = explode("\n", $summary);
            $header = str_getcsv($lines[0]);
            for ($i = 1; $i < count($lines); $i++) {
                $row = str_getcsv($lines[$i]);
                if (!sscanf($row[0], "%d.%d.%d", $d, $m, $y))
                    die("sscanf\n");
                $row[0] = sprintf("%04d-%02d-%02d", $y, $m, $d);
                $crow = array();
                for ($j = 1; $j < count($row); $j++) {
                    $crow[$header[$j]] = $row[$j];
                }
                if (count($crow) > 0) {
                    $data[$row[0]] = $crow;
                }
            }
            file_put_contents($cache_filename, json_encode($data, JSON_NUMERIC_CHECK), LOCK_EX);
        }
    }
    $data = json_decode(file_get_contents($cache_filename), true);

    return $data;
}


$zarazeni = data_set('zarazeni.csv', 8);
$izlijeceni = data_set('izlijeceni.csv', 8);
$summary = get_summary();

$data = array('zarazeni' => $zarazeni, 'izlijeceni' => $izlijeceni, 'skupno' => $summary);

header("Content-type: application/json");
echo json_encode($data, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK);
echo "\n";


