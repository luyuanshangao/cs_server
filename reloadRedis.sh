###
 # @Author: gz
 # @Date: 2020-12-22 10:13:10
 # @LastEditors: gz
 # @LastEditTime: 2020-12-22 10:34:14
 # @Description: file content
 # @FilePath: \cs_server\reloadRedis.sh
### 
ps=`ps -efl|grep redis|grep -v $0|grep -v grep|wc -l`
if [ $ps -eq 0 ];
then
    echo -e "\n$(date '+%Y-%m-%d %H:%M:%S') start "
    systemctl restart redis
    echo "$(date '+%Y-%m-%d %H:%M:%S') done"
else
    echo $(date +%F%n%T) "redis正在运行..."
    exit 0;
fi
