<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
          content="御宅云加速器，采用开源加速引擎,顶级IDC集群，全线高端刀片服务器！为网游用户解决延迟、掉线、卡机等问题，让你游戏更爽快！告别高延迟！外服加速72小时免费试用。海外直连专线，外服游戏加速效果业界顶尖！支持加速绝地求生、H1Z1、GTA5、CS:GO，以及LOL英雄联盟、DNF地下城与勇士、CF穿越火线、CSGO等上百款热门中外网游。">
    <meta name="keywords" content="御宅云 绅士世界 加速器 网游加速器 外服加速器 超快感 远征军 海外游戏加速 steam加速 免费加速器 游戏加速 H1Z1加速器 绝地求生加速器 大逃杀加速 绝地加速 GTA加速 CSGO加速">
    <meta name="author" content="绅士世界御宅云">
    <meta name="copyright" content="御宅云">
    <title>@yield('title')</title>
    <link href="{{ asset('favicon.ico') }}" rel="shortcut icon apple-touch-icon">
    <!-- 样式表/Stylesheets -->
    <link href="/assets/bundle/app.min.css" rel="stylesheet">
    <link href="https://cdn.staticfile.net/flag-icons/7.2.3/css/flag-icons.min.css" rel="stylesheet">
    @yield('layout_css')
    <!-- 字体/Fonts -->
    <link href="/assets/global/fonts/web-icons/web-icons.min.css" rel="stylesheet">
    <link href="https://fonts.loli.net" rel="preconnect">
    <link href="https://gstatic.loli.net" rel="preconnect" crossorigin>
    <link href="https://fonts.loli.net/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap"
          rel="stylesheet">
    <!-- Scripts -->
    <script src="/assets/global/vendor/breakpoints/breakpoints.min.js"></script>
    <script>
        Breakpoints();
    </script>
    @if (config('theme.skin'))
        <link id="skinStyle" href="/assets/css/skins/{{ config('theme.skin') }}.min.css" rel="stylesheet">
    @endif
</head>

<body class="animsition @yield('body_class')">
    @yield('layout_content')
    <!-- 核心/Core -->
    <script src="/assets/global/vendor/babel-external-helpers/babel-external-helpers.js"></script>
    <script src="/assets/global/vendor/jquery/jquery.min.js"></script>
    <script src="/assets/global/vendor/popper-js/umd/popper.min.js"></script>
    <script src="/assets/global/vendor/bootstrap/bootstrap.min.js"></script>
    <script src="/assets/global/vendor/animsition/animsition.min.js"></script>
    <script src="/assets/global/vendor/mousewheel/jquery.mousewheel.min.js"></script>
    <script src="/assets/global/vendor/asscrollbar/jquery-asScrollbar.min.js"></script>
    <script src="/assets/global/vendor/asscrollable/jquery-asScrollable.min.js"></script>
    <script src="/assets/global/vendor/ashoverscroll/jquery-asHoverScroll.min.js"></script>
    <!-- 插件/Plugins -->
    <script src="/assets/global/vendor/screenfull/screenfull.min.js"></script>
    <script src="/assets/global/vendor/slidepanel/jquery-slidePanel.min.js"></script>
    <!-- 脚本/Scripts -->
    <script src="/assets/global/js/Component.js"></script>
    <script src="/assets/global/js/Plugin.js"></script>
    <script src="/assets/global/js/Base.js"></script>
    <script src="/assets/global/js/Config.js"></script>
    <script src="/assets/js/Section/Menubar.js"></script>
    <script src="/assets/js/Section/Sidebar.js"></script>
    <script src="/assets/js/Section/PageAside.js"></script>
    <script src="/assets/js/Plugin/menu.js"></script>
    <!-- 设置/Config -->
    <script src="/assets/global/js/config/colors.js"></script>
    <script>
        Config.set("assets", "/assets");
    </script>
    <!-- 页面/Page -->
    <script src="/assets/js/Site.js"></script>
    <script src="/assets/global/js/Plugin/asscrollable.js"></script>
    <script src="/assets/global/js/Plugin/slidepanel.js"></script>
    <script>
        (function(document, window, $) {
            "use strict";
            const Site = window.Site;
            $(document).ready(function() {
                Site.run();
            });
        })(document, window, jQuery);
        @auth
        document.addEventListener("DOMContentLoaded", async function() {
            const avatarElements = Array.from(document.querySelectorAll("img[data-uid]"));
            let avatarData = JSON.parse(localStorage.getItem("avatarData")) || {};
            const fetchPromises = {};

            // Group img elements by uid
            const uidToElementsMap = groupElementsByUid(avatarElements);

            for (const [uid, elements] of Object.entries(uidToElementsMap)) {
                if (avatarData[uid]) {
                    updateElementsSrc(elements, avatarData[uid]);
                } else if (!fetchPromises[uid]) {
                    const {
                        username,
                        qq
                    } = elements[0].dataset;
                    fetchPromises[uid] = fetchAndCacheAvatar(uid, username, qq).then(imgUrl => {
                        avatarData[uid] = imgUrl;
                        localStorage.setItem("avatarData", JSON.stringify(avatarData));
                        updateElementsSrc(elements, imgUrl);
                    }).catch(error => {
                        console.error(`Error fetching avatar for uid ${uid}:`, error);
                        updateElementsSrc(elements, ""); // Or set to a default URL
                    });
                }
            }
        });

        function groupElementsByUid(elements) {
            return elements.reduce((acc, el) => {
                const uid = el.dataset.uid;
                if (!acc[uid]) acc[uid] = [];
                acc[uid].push(el);
                return acc;
            }, {});
        }

        function updateElementsSrc(elements, src) {
            elements.forEach(el => el.src = src);
        }

        async function fetchAndCacheAvatar(uid, username, qq) {
            const response = await fetch(`{{ route('getAvatar') }}?username=${username}&qq=${qq}`);
            const url = await response.json();
            if (/@qq\.com/.test(username) || qq) {
                return url;
            }
            const imgResponse = await fetch(url);

            if (url === imgResponse.url) {
                const type = imgResponse.headers.get("Content-Type");
                const imgBlob = await imgResponse.blob();
                const base64String = await blobToBase64(imgBlob);
                return `data:${type};base64,${base64String}`;
            } else {
                return imgResponse.url;
            }
        }

        async function blobToBase64(blob) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onload = () => resolve(reader.result.split(",")[1]);
                reader.onerror = () => reject("Error converting blob to base64");
                reader.readAsDataURL(blob);
            });
        }
        @endauth
    </script>
    @yield('layout_javascript')
</body>

</html>
