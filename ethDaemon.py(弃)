#!/usr/bin/python
# coding:utf-8

import sys
import os

import time
import commands
import requests
import json


def send(url, data):
    headers = {'Content-Type': "application/json"}
    # 提交post请求
    P_post = requests.post(url, headers=headers, data=data)
    return P_post


# 获取运行参数
args = sys.argv
if (len(args) < 2):
    print("error cli")
    exit(0)
cli = args[1]

if cli == "start":
    print("正在启动服务")
    # 查看当前是否有正在运行的程序
    outInfo = commands.getoutput(
        "ps ax |grep '" + os.path.realpath(__file__) + " run' |grep -v grep |wc -l")
    if (outInfo == "0"):
        # 运行程序守护
        os.system("nohup python " + os.path.realpath(__file__) +
                  " run > /dev/null 2>&1 &")
        print("启动成功")
    else:
        print("不可重复运行")
elif cli == "run":
    while 1:
        data = send(
            "http://api.coinshop.vip/queue/EthQueue/getAddress", "").json()

        for address in data:
            # 获取地址余额
            post_data = {"jsonrpc": "2.0", "method": "eth_getBalance",
                         "params": [address, "latest"], "id": 1}
            balance = send("http://127.0.0.1:10123",
                           json.dumps(post_data)).json()
           
            if "error" in balance.keys():
                continue

            amount = balance["result"]
            amount = int(amount, 16)
           
            if amount > 0:
                fp = open("/root/eth.log", "a")
                fp.write(address + "\n")
                fp.close()
                # 将余额转移
                amount = float(amount) / 1000000000 / 1000000000
                post_data = {"address": address, "amount": amount}
                status = send(
                    "http://api.coinshop.vip/queue/EthQueue/sendETH", json.dumps(post_data))
                if status.status_code != 200:
                    fp = open("/root/eth.log", "a")
                    fp.write(address + " send error !!!!\n")
                    fp.close()
                    continue

                # 用户充值到账
                post_data = {"address": address,
                             "amount": amount, "pass": "pFFXjqUvcvNde2To"}
                send("http://api.coinshop.vip/queue/EthQueue/addETH",
                     json.dumps(post_data))

        time.sleep(2)
