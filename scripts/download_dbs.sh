#!/bin/bash

FILE_DIR="../database"
VERSION_FILE="$FILE_DIR/version.json"
source ./download_utils.sh

# 检查 jq 是否已安装
check_and_install jq

# 数据库信息
geo_lite_version=$(get_tag "PrxyHunter/GeoLite2")
geo_lite_url="https://github.com/PrxyHunter/GeoLite2/releases/download/$geo_lite_version/GeoLite2-City.mmdb"

ip2location_version=$(get_tag "renfei/ip2location")
ip2location_url="https://github.com/renfei/ip2location/releases/download/$ip2location_version/IP2LOCATION-LITE-DB11.IPV6.BIN"

qqwry_version=$(get_tag "metowolf/qqwry.dat")
qqwry_url="https://cdn.jsdelivr.net/npm/qqwry.ipdb/qqwry.ipdb"

declare -Ag docs

docs[geo_lite_name]="GeoLite2-City.mmdb"
docs[geo_lite_version]="$geo_lite_version"
docs[geo_lite_url]="$geo_lite_url"

docs[ip2location_name]="IP2LOCATION-LITE-DB11.IPV6.BIN"
docs[ip2location_version]="$ip2location_version"
docs[ip2location_url]="$ip2location_url"

docs[qqwry_name]="qqwry.ipdb"
docs[qqwry_version]="$qqwry_version"
docs[qqwry_url]="$qqwry_url"

# 主逻辑
process_files
echo "Update completed!"

exit 0
