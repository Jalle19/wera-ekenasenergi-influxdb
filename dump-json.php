<?php

// Parse options
$longOpts = [
    'username:',
    'password:',
    'customerCode:',
    'networkCode:',
    'meteringPointCode:',
];

$options = getopt('', $longOpts);

if (count($options) !== 5) {
    $usageOptionsString = <<<OPTS

  --username <username> 
  --password <password> 
  --customerCode <customerCode> 
  --networkCode <networkCode> 
  --meteringPointCode <meteringPointCode>
OPTS;

    die('Usage: ' . $argv[0] . $usageOptionsString . "\n");
}

// Create a temporary file for storing cookies
$cookieJarPath = tempnam(sys_get_temp_dir(), 'wera-ekenasenergi-influxdb');

// Perform a GET request to the IndexNoAuth page and extract the hidden __RequestVerificationToken form value
$ch = curl_init('https://wera.ekenasenergi.fi/eServices/Online/IndexNoAuth');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJarPath);
$loginPageHtml = curl_exec($ch);
$loginPage = new DOMDocument();
@$loginPage->loadHTML($loginPageHtml);
$loginPageXp = new DOMXPath($loginPage);
$hiddenInputField = $loginPageXp->query('//input[@name="__RequestVerificationToken"]');

if ($hiddenInputField === false || $hiddenInputField->count() !== 1) {
    throw new RuntimeException('Failed to get a request verification token');
}

$requestVerificationToken = $hiddenInputField->item(0)->getAttribute('value');

// Now that we have a session we can perform a POST to Login to authenticate ourselves
$ch = curl_init('https://wera.ekenasenergi.fi/eServices/Online/Login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJarPath);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    '__RequestVerificationToken' => $requestVerificationToken,
    'UserName' => $options['username'],
    'Password' => $options['password'],
]));
curl_exec($ch);

// Now that we're authenticated we can get the actual measurement data
$ch = curl_init('https://wera.ekenasenergi.fi/Reporting/CustomerConsumption/GetHourlyConsumption');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJarPath);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'customerCode' => $options['customerCode'],
    'networkCode' => $options['networkCode'],
    'meteringPointCode' => $options['meteringPointCode'],
    'enableTemperature' => 'true',
    'enablePriceSeries' => 'true',
    'enableTemperatureCorrectedConsumption' => 'true',
]));
$consumptionJson = curl_exec($ch);

// Pretty print the output
echo json_encode(json_decode($consumptionJson), JSON_PRETTY_PRINT);
