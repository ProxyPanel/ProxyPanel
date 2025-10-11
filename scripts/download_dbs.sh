#!/bin/bash

# 设置文件目录和版本文件
FILE_DIR="../database"
VERSION_FILE="$FILE_DIR/version.json"

# 引入下载工具函数
source ./download_utils.sh

# 检查并安装依赖软件包
check_and_install jq

# 定义数据库信息
declare -Ag docs

# 设置数据库信息
docs[geo_lite_name]="GeoLite2-City.mmdb"
docs[geo_lite_version]=$(get_tag "P3TERX/GeoLite.mmdb")
docs[geo_lite_url]="https://github.com/P3TERX/GeoLite.mmdb/releases/download/${docs[geo_lite_version]}/GeoLite2-City.mmdb"

docs[ip2location_name]="IP2LOCATION-LITE-DB11.IPV6.BIN"
docs[ip2location_version]=$(get_tag "renfei/ip2location")
docs[ip2location_url]="https://github.com/renfei/ip2location/releases/download/${docs[ip2location_version]}/IP2LOCATION-LITE-DB11.IPV6.BIN"

docs[qqwry_name]="qqwry.ipdb"
docs[qqwry_version]=$(get_tag "metowolf/qqwry.dat")
docs[qqwry_url]="https://cdn.jsdelivr.net/npm/qqwry.ipdb/qqwry.ipdb"

# 处理文件下载
process_files
echo -e "\e[32mUpdate completed!\e[0m"