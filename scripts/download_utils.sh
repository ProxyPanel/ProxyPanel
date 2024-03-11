#!/bin/bash
# 信号处理
trap 'rm -rf /tmp; exit 130' SIGINT SIGTSTP SIGTERM

# 检查并安装软件包
check_and_install() {
  local pkg=$1

  if ! command -v "$pkg" >/dev/null 2>&1; then
    if command -v apt-get >/dev/null 2>&1; then
      sudo apt-get update
      sudo apt-get install -y "$pkg"
    elif command -v yum >/dev/null 2>&1; then
      sudo yum install -y epel-release
      sudo yum install -y "$pkg"
    elif command -v dnf >/dev/null 2>&1; then
      sudo dnf install -y "$pkg"
    elif command -v pacman >/dev/null 2>&1; then
      sudo pacman -S "$pkg"
    elif command -v zypper >/dev/null 2>&1; then
      sudo zypper install -y "$pkg"
    else
      echo -e "\e[31m无法安装 $pkg，不支持的 Linux 发行版\e[0m"
      exit 1
    fi
  fi
}

# 获取 GitHub 仓库的最新标签
get_tag() {
  local repo=$1
  curl -fsSL "https://api.github.com/repos/$repo/releases/latest" | jq -r '.tag_name'
}

# 定义下载函数
download_file() {
  local name=$1
  local version=$2
  local url=$3
  local tmp_file="/tmp/$name.tmp"
  local local_version=$(jq -r ".[\"$name\"]" <"$VERSION_FILE" 2>/dev/null || echo "0.0.0")

  echo -e "\e[1;47;34m$name 版本信息：【本地版本】$local_version | 【最新版本】$version\e[0m"

  if [ "$version" != "$local_version" ]; then
    echo -e "\e[37m正在更新 $name 到版本 $version\e[0m"

    # 下载文件
    if ! curl -I -L -m 10 "$url" >/dev/null 2>&1; then
      echo -e "\e[31mURL $url 不存在\e[0m"
      return 2
    fi
    if ! curl -L -m 60 -o "$tmp_file" "$url"; then
      echo -e "\e[31m下载 $name 失败\e[0m"
      rm -f "$tmp_file"
      return 1
    fi

    # 验证文件完整性
    local actual_size=$(du -b "$tmp_file" | awk '{print $1}')
    local min_size=$((1048576)) # 1MB minimum file size

    if [ "$actual_size" -lt "$min_size" ]; then
      echo -e "\e[31m[取消]下载的文件大小小于1MB，文件可能不完整\e[0m"
      rm -f "$tmp_file"
      return 1
    fi

    # 下载成功，重命名文件
    mv "$tmp_file" "$FILE_DIR/$name"
    echo -e "\e[32m成功更新 $name 到版本 $version\e[0m"

    return 0
  fi
}

# 处理文件下载
process_files() {
  local json="{}"
  for doc in "${!docs[@]}"; do
    if [[ $doc == *_name ]]; then
      local name=${docs[$doc]}
      local version=${docs[${doc/_name/_version}]}
      local url=${docs[${doc/_name/_url}]}

      download_file "$name" "$version" "$url"
      json=$(jq -r --arg name "$name" --arg version "$version" '.[$name]=$version' <<<"$json")
    fi
  done

  echo "$json" >"$VERSION_FILE"
}
