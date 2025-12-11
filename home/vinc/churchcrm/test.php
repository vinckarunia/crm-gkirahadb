<?php
set_time_limit(0); // Remove time limit
ini_set('memory_limit', '1024M');

echo "Testing CSV processing speed...<br>";
$start_time = microtime(true);

// Simulate reading your CSV
$file = 'your_csv_file.csv'; // Replace with actual filename
if (($handle = fopen($file, "r")) !== FALSE) {
    $row_count = 0;
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $row_count++;
        if ($row_count % 1000 == 0) {
            echo "Processed $row_count rows...<br>";
            flush();
        }
    }
    fclose($handle);
    
    $end_time = microtime(true);
    $execution_time = ($end_time - $start_time);
    
    echo "Total rows: $row_count<br>";
    echo "Execution time: " . round($execution_time, 2) . " seconds<br>";
    echo "Rows per second: " . round($row_count / $execution_time) . "<br>";
}
?>