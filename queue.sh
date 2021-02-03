#!/bin/sh
curl http://api.coinshop.vip/queue/OrderQueue/editOrderStatusBySanfang
curl http://api.coinshop.vip/queue/OrderQueue/editOrderServiceStatusBySanFang
curl http://api.coinshop.vip/queue/OrderQueue/delOrderDelayed
curl http://api.coinshop.vip/queue/OrderQueue/checkPromotionIncome
curl http://api.coinshop.vip/queue/OrderQueue/checkPromotionAuth
curl http://api.coinshop.vip/queue/OrderQueue/upRate

curl http://api.coinshop.vip/queue/BitcoinQueue/listener


