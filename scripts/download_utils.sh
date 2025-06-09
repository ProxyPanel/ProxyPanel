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
    elif [[ -f /etc/alpine-release ]]; then
      sudo apk add --no-cache "$pkg"
    else
      echo -e "\e[31m无法安装 $pkg，不支持的 Linux 发行版\e[0m"
      exit 1
    fi
  fi
}

# 获取 GitHub 仓库的最新标签
get_tag() {
  local repo=$1
    local headers=("-H" "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36")  # 合法 User-Agent

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
    if ! curl -L -m 60 -H "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36" --retry 3 --retry-delay 5 -o "$tmp_file" "$url"; then
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
  # 读取当前版本信息（如果存在）
  local current_versions
  if [ -f "$VERSION_FILE" ]; then
    current_versions=$(cat "$VERSION_FILE")
  else
    current_versions="{}"
  fi

  # 创建临时版本文件
  local tmp_version_file="/tmp/version.json.tmp"
  echo "$current_versions" > "$tmp_version_file"

  # 遍历所有文档
  for doc in "${!docs[@]}"; do
    if [[ $doc == *_name ]]; then
      local name=${docs[$doc]}
      local version=${docs[${doc/_name/_version}]}
      local url=${docs[${doc/_name/_url}]}

      # 下载文件并检查状态
      if download_file "$name" "$version" "$url"; then
        # 下载成功，更新临时版本文件
        jq -r --arg name "$name" --arg version "$version" '.[$name]=$version' \
          <"$tmp_version_file" > "$tmp_version_file.tmp"
        mv "$tmp_version_file.tmp" "$tmp_version_file"
      else
        # 下载失败，保留旧版本（如果存在）
        local old_version=$(jq -r ".[\"$name\"]" <"$tmp_version_file" 2>/dev/null)
        echo -e "\e[33m[跳过] $name 版本保持为 ${old_version:-未安装}\e[0m"
      fi
    fi
  done

  # 原子化替换原版本文件
  mv "$tmp_version_file" "$VERSION_FILE"
  echo -e "\e[32m版本文件已更新（仅包含成功下载的条目）\e[0m"
}
