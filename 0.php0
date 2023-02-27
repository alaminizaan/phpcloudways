<?php

// Set API endpoint
define('API_ENDPOINT', 'https://api.binance.com/api/v3/ticker/price');

// Fetch all ticker prices from Binance API
$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => API_ENDPOINT,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json'
  ),
));
$response = curl_exec($curl);
curl_close($curl);

// Parse the response JSON and create a dictionary with coin symbols as keys and prices as values
$prices = json_decode($response, true);
$all_prices = array();
foreach ($prices as $price) {
  $symbol = $price['symbol'];
  $price = floatval($price['price']);
  if (strpos($symbol, 'USDT') !== false) {
    $all_prices[substr($symbol, 0, -4)] = $price;
  } else if (strpos($symbol, 'USD') !== false) {
    $all_prices[substr($symbol, 0, -3)] = $price;
  }
}

// Print the prices
foreach ($all_prices as $coin => $price) {
  printf("%s: %.4f USD<br>\n", $coin, $price);
}

?>
