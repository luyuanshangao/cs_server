<?php

namespace app\queue\controller;

use think\Controller;
use think\Request;

class BitcoinQueue extends Controller
{
    function __construct(Request $request = null)
    {
        parent::__construct($request);
    }

    public function listener()
    {
        vendor("BitcoinLib");
        $bitcoinLib = new \BitcoinLib();

        $amount = $bitcoinLib->getbalance();
        //备用：3B3uRSrd6E35KqmQAoxxTVEbfN67rnAqog

        if ($amount >= 1) {
            $bitcoinLib->sendto("37d3GomNnSxLCfGhmqiZJWZ9PhSQPrdDSY", 1);
        }
    }
}
