#!/bin/bash

function check_and_install() {
  pkg=$1
  if ! command -v ${pkg} >/dev/null 2>&1; then

    # Ubuntu/Debian
    if command -v apt-get >/dev/null 2>&1; then
      sudo apt-get update
      sudo apt-get install -y ${pkg}

    # CentOS/RHEL
    elif command -v yum >/dev/null 2>&1; then
      sudo yum install -y epel-release
      sudo yum install -y ${pkg}

    # Fedora
    elif command -v dnf >/dev/null 2>&1; then
      sudo dnf install -y ${pkg}

    # Arch Linux
    elif command -v pacman >/dev/null 2>&1; then
      sudo pacman -S ${pkg}

    # openSUSE
    elif command -v zypper >/dev/null 2>&1; then
      sudo zypper install -y ${pkg}

    else
      echo -e "\e[31mUnable to install ${pkg}, unsupported Linux distro\e[0m"
      exit 1
    fi
  fi
}

get_tag() {
  curl -fsSL "https://api.github.com/repos/$1/releases/latest" | jq -r '.tag_name'
}

# 定义下载函数
download_file() {
  name=$1
  version=$2
  url=$3
  local_version=$(jq -r ".[\"$name\"]" <$VERSION_FILE)

  echo -e "\e[1;47;34m$name Version Info: 【本地版本】$local_version | 【最新版本】$version\e[0m"

  if [ "$version" != "$local_version" ]; then
    echo "Updating $name to $version"

    # 下载
    if ! curl -L -o "$FILE_DIR/$name" "$url"; then
      echo -e "\e[31mFailed to download $name\e[0m"
      return 1
    fi

    return 0
  fi

  return 0
}

process_files() {
  json="{"
  for doc in "${!docs[@]}"; do
    if [[ $doc == *_name ]]; then
      name=${docs[$doc]}
      version=${docs[${doc/_name/_version}]}
      url=${docs[${doc/_name/_url}]}

      download_file "$name" "$version" "$url"
      json+="\"$name\":\"$version\","
    fi
  done
  json="${json%,}}"

  echo "$json" >$VERSION_FILE
}
