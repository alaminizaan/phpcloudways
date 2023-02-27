<?php
require 'vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise;

// Set API endpoint and trading fee
define('API_ENDPOINT', 'https://api.binance.com/api/v3/ticker/price');
define('TRADING_FEE', 0.001);

// Fetch all ticker prices from Binance API asynchronously using Guzzle
$client = new Client();
$promises = [
    'USDT' => $client->getAsync(API_ENDPOINT.'?symbol=USDTUSDT'),
    'BTC' => $client->getAsync(API_ENDPOINT.'?symbol=BTCUSDT'),
    'ETH' => $client->getAsync(API_ENDPOINT.'?symbol=ETHUSDT'),
    'BNB' => $client->getAsync(API_ENDPOINT.'?symbol=BNBUSDT'),
    'XRP' => $client->getAsync(API_ENDPOINT.'?symbol=XRPUSDT'),
    'USDC' => $client->getAsync(API_ENDPOINT.'?symbol=USDCUSDT'),
    'DOGE' => $client->getAsync(API_ENDPOINT.'?symbol=DOGEUSDT'),
    'DOT' => $client->getAsync(API_ENDPOINT.'?symbol=DOTUSDT'),
];

$all_prices = array();
foreach (Promise\settle($promises)->wait() as $symbol => $promise) {
    try {
        $response = $promise['value']->getBody();
        $price = json_decode($response, true)['price'];
        $all_prices[$symbol] = $price;
    } catch (RequestException $e) {
        // Handle error
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
printf('<span style="color:%s">Triangular Arbitrage Opportunity: %s, Profit: %.2f%%, USD Amount: $%.2f</span><br>',
$color,
$coins,
$profit * 100,
$usd_amount
);
}
