
<?php

/**
 * Document   : Stock's Simple Moving Average Class
 * Author     : josephtinsley
 * Description: PHP class that calculates a stocks simple moving average
 * http://twitter.com/josephtinsley 
*/

class SMA {

    public $google_finance_url  = "http://www.google.com/finance/historical?q=NASDAQ%3AAAPL&ei=T6lSWNCILMLomAGipJ7ADw&output=csv";
    public $cache_file_location = "SimpleMovingAverages/stock_prices.txt";
    public $days_to_limit       = 20;
    public $closing_prices      = [];
    public $shifted_prices      = [];

    public function get_closing_prices() 
    {

        if(!file_exists($this->cache_file_location))
        {

            $url = $this->google_finance_url;
            $data = file_get_contents($url);
            $rows = explode("\n", $data);

            for ($x = 1; $x < count($rows); $x++) 
            {
                $stock_prices[] = explode(",", $rows[$x]); 
            }

            $closing_prices_json = json_encode($stock_prices);
            $fd = fopen($this->cache_file_location, 'w');
            $out = print_r($closing_prices_json, true);
            fwrite($fd, $out);
        }

        $closing_prices_json = file_get_contents($this->cache_file_location);
        $closing_prices      = json_decode($closing_prices_json,TRUE);
        $this->closing_prices = $closing_prices;
    } 


    public function price_shift() 
    {
        $shifted_prices[] = $this->closing_prices;
        foreach($this->closing_prices as $p)
        {
            array_shift($this->closing_prices);
            $shifted_prices[] = $this->closing_prices;
        }
        $this->shifted_prices = $shifted_prices;
    }
    
    private function _sma_averages($prices) 
    {
        for ($x = 0; $x < $this->days_to_limit; $x++) 
        {
            $c_prices[] = $prices[$x][4];
        }
        return array_sum($c_prices)/$this->days_to_limit;
    } 
    
    public function result_set() 
    {
        $prices = $results = [];
        for ($x = 0; $x < count($this->shifted_prices); $x++) 
        {
            if( count($this->shifted_prices[$x]) >= $this->days_to_limit )
            {
                $prices['DATE']    = $this->shifted_prices[$x][0][0];
                $prices['OPEN']    = $this->shifted_prices[$x][0][1];
                $prices['HIGH']    = $this->shifted_prices[$x][0][2];
                $prices['LOW']     = $this->shifted_prices[$x][0][3];
                $prices['CLOSE']   = $this->shifted_prices[$x][0][4];
                $prices['VOL']     = $this->shifted_prices[$x][0][5];
                $prices['SMA']     = $this->_sma_averages($this->shifted_prices[$x]);
                $results[] = $prices;
            } 
        }
        return $results;
    } 

}//END CLASS

$SMA = new SMA();
$SMA->get_closing_prices();
$SMA->price_shift();
$results = $SMA->result_set();

        
print "<pre>";
print_r($results);
print "</pre>";
