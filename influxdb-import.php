<?php

// Parse options
$longOpts = [
    'influxDbUrl:',
    'influxDbName:',
    'influxDbUsername:',
    'influxDbPassword:',
];

$options = getopt('', $longOpts);

if (count($options) !== 4) {
    $usageOptionsString = <<<OPTS

  --influxDbUrl <influxDbUrl> 
  --influxDbName <influxDbName> 
  --influxDbUsername <influxDbUsername> 
  --influxDbPassword <influxDbPassword>
  < <jsonFile>
OPTS;

    die('Usage: ' . $argv[0] . $usageOptionsString . " \n");
}

// Read the JSON from stdin
$json = file_get_contents('php://stdin');
$data = json_decode($json, true);

// We'll generate two measurements, Consumptions and Temperature. We filter out empty 
// values since there's no way of telling how far the data set goes (it tends to go into the future).
function extractFilteredDataSeries(array $data)
{
    $extractedData = array_map(static function (array $dataPoint) {
        // Timestamps are in UNIX microseconds. Convert these to UNIX nanoseconds
        $dataPoint[0] *= 1000 * 1000;

        return $dataPoint;
    }, $data);

    // Starting from the back, find the first data point with a non-zero value. We can't
    // simply filter out zero values since temperature may very well be zero.
    for ($i = count($extractedData) - 1; $i > 0; $i--) {
        if ($extractedData[$i][1] !== 0) {
            break;
        }

        array_pop($extractedData);
    }

    return $extractedData;
}

$consumptions = extractFilteredDataSeries($data['Consumptions'][0]['Series']['Data']);
$temperature = extractFilteredDataSeries($data['Temperature']['Data']);

// Generate InfluxDB queries
$queries = array_map(static function (array $dataPoint) {
    return sprintf('Consumptions PowerConsumption=%f %d', $dataPoint[1], $dataPoint[0]);
}, $consumptions);

$queries = array_merge($queries, array_map(static function (array $dataPoint) {
    return sprintf('Consumptions Temperature=%f %d', $dataPoint[1], $dataPoint[0]);
}, $temperature));

// Write all the data points in one go to InfluxDB
// Write the measurements to InfluxDB
$combinedQuery = implode("\n", $queries);

$writeContext = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: text/plain',
        'content' => $combinedQuery,
    ],
]);

$writeApiUrl = sprintf('%s/write?db=%s', rtrim($options['influxDbUrl'], '/'), $options['influxDbName']);
$writeResponse = file_get_contents($writeApiUrl, false, $writeContext);

if ($writeResponse === false) {
    throw new RuntimeException('Failed to write data points to InfluxDB');
}
