#!/bin/bash

db_dir="./database"
version_file="$db_dir/version.json"

# 检查 jq 是否已安装
if ! command -v jq >/dev/null 2>&1; then

  # Ubuntu/Debian
  if command -v apt-get >/dev/null 2>&1; then
    sudo apt-get update
    sudo apt-get install -y jq

  # CentOS/RHEL
  elif command -v yum >/dev/null 2>&1; then
    sudo yum install -y epel-release
    sudo yum install -y jq

  # Fedora
  elif command -v dnf >/dev/null 2>&1; then
    sudo dnf install -y jq

  # Arch Linux
  elif command -v pacman >/dev/null 2>&1; then
    sudo pacman -S jq

  # openSUSE
  elif command -v zypper >/dev/null 2>&1; then
    sudo zypper install -y jq

  else
    echo -e "\e[31mUnable to install jq, unsupported Linux distro\e[0m"
    exit 1
  fi
fi

# 如果版本文件不存在,创建一个空的
if [ ! -f "$version_file" ]; then
  echo '{"GeoLite2":"0.0","IP2Location":"0.0","qqwry":"0.0"}' >"$version_file"
fi

# 读取本地版本信息
geo_lite_local=$(jq -r .GeoLite2 $version_file)
ip2location_local=$(jq -r .IP2Location $version_file)
qqwry_local=$(jq -r .qqwry $version_file)

# 获取线上的最新版本
geo_lite_remote=$(curl -s https://api.github.com/repos/PrxyHunter/GeoLite2/releases/latest | grep tag_name | cut -d\" -f4)
ip2location_remote=$(curl -s https://api.github.com/repos/renfei/ip2location/releases/latest | grep tag_name | cut -d\" -f4)
qqwry_remote=$(curl -s https://api.github.com/repos/metowolf/qqwry.dat/releases/latest | grep tag_name | cut -d\" -f4)

echo -e "\e[1;47;34mGeoLite2 Version Info: 【本地版本】$geo_lite_local | 【最新版本】$geo_lite_remote\e[0m"
echo -e "\e[1;47;34mIP2Location Version Info: 【本地版本】$ip2location_local | 【最新版本】$ip2location_remote\e[0m"
echo -e "\e[1;47;34mQQwry Version Info: 【本地版本】$qqwry_local | 【最新版本】$qqwry_remote\e[0m"

# 如果线上版本大于本地版本,则执行下载更新
if [ "$geo_lite_remote" != "$geo_lite_local" ]; then
  # 下载GeoLite2的代码
  echo "Updating GeoLite2 to $geo_lite_remote"
  # Download the latest release of GeoLite2-City.mmdb from PrxyHunter/GeoLite2
  url=$(curl -s https://api.github.com/repos/PrxyHunter/GeoLite2/releases/latest | grep "browser_download_url.*Country.mmdb" | cut -d : -f 2,3 | tr -d \")
  if ! curl -L -S $url -o $db_dir/GeoLite2-City.mmdb; then
    echo -e "\e[31mFailed to download GeoLite2\e[0m"
  fi
fi

if [ "$ip2location_remote" != "$ip2location_local" ]; then
  # 下载IP2Location的代码
  echo "Updating IP2Location to $ip2location_remote"
  # Download the latest release of IP2LOCATION-LITE-DB11.IPV6.BIN from renfei/ip2location
  url=$(curl -s https://api.github.com/repos/renfei/ip2location/releases/latest | grep "browser_download_url.*IP2LOCATION-LITE-DB11.IPV6.BIN" | cut -d : -f 2,3 | tr -d \")
  if ! curl -L -S $url -o $db_dir/IP2LOCATION-LITE-DB11.IPV6.BIN; then
    echo -e "\e[31mFailed to download IP2Location\e[0m"
  fi
fi

if [ "$qqwry_remote" != "$qqwry_local" ]; then
  # 下载qqwry的代码
  echo "Updating qqwry to $qqwry_remote"
  if ! curl -L -S https://cdn.jsdelivr.net/npm/qqwry.ipdb/qqwry.ipdb -o $db_dir/qqwry.ipdb; then
    echo -e "\e[31mFailed to download qqwry\e[0m"
  fi
fi

# 更新版本文件
echo "{\"GeoLite2\":\"$geo_lite_remote\",\"IP2Location\":\"$ip2location_remote\",\"qqwry\":\"$qqwry_remote\"}" >"$version_file"
