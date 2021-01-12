#!/usr/bin/python
# coding:utf-8
import os
import commands
print("检查swoole服务运行状态")
# 查看当前是否有正在运行的程序
result = commands.getoutput("netstat -anp 2>/dev/null | grep  9595 | grep LISTEN | wc -l")
if (result == "0"):
    # 
    os.system("nohup php /www/cs_server/swoole/Update.php >/dev/null 2>&1 &")
    print("已运行")
else:
    print("运行中")

