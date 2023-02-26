<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set API endpoint and trading fee
define('API_ENDPOINT', 'https://api.binance.com/api/v3/ticker/price');
define('TRADING_FEE', 0.001);

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
if ($response === false) {
  die(curl_error($curl));
}
curl_close($curl);

// Parse the response JSON and create a dictionary with coin symbols as keys and prices as values
$prices = json_decode($response, true);
if ($prices === null || !is_array($prices)) {
  die('Invalid JSON response from API');
}
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

// Calculate the triangular arbitrage opportunities
$opportunities = array();
foreach ($all_prices as $coin1 => $price1) {
  foreach ($all_prices as $coin2 => $price2) {
    foreach ($all_prices as $coin3 => $price3) {
      if ($coin1 != $coin2 && $coin1 != $coin3 && $coin2 != $coin3) {
        // Check if the coins form a valid triangular arbitrage opportunity
        if (array_key_exists($coin2, $all_prices) && array_key_exists($coin3, $all_prices)) {
          $rate1 = $price1 / $all_prices[$coin2];
          $rate2 = $price2 / $all_prices[$coin3];
          $rate3 = $price3 / $price1;
          $profit = $rate1 * $rate2 * $rate3 * (1 - TRADING_FEE) * (1 - TRADING_FEE) * (1 - TRADING_FEE) - 1;
          if ($profit > 0.003) {
            $opportunities[] = array(
              'coins' => "$coin1 -> $coin2 -> $coin3 -> $coin1",
              'profit' => $profit
            );
          }
        }
      }
    }
  }
}

// Sort the opportunities by profit and print the results
usort($opportunities, function($a, $b) {
  return $b['profit'] <=> $a['profit'];
});
foreach ($opportunities as $opportunity) {
  $coins = $opportunity['coins'];
  $profit = $opportunity['profit'];
  $usd_amount = $profit * 10000; // Assuming 10,000 USD investment
  $color = ($profit > 0) ? 'green' : 'red';
  printf("%s: <span style='color:%s'>%.2f%% (%.2f USD)</span><br>\n", $coins, $color, $profit * 100, $usd_amount);
}

?>
