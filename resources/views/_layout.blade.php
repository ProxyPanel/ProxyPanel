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
    <link href="https://fonts.loli.net" rel="preconnect" crossorigin>
    <link href="https://gstatic.loli.net" rel="preconnect" crossorigin>
    <link href="https://cdn.jsdelivr.net" rel="preconnect" crossorigin>
    <link href="https://cdn.jsdelivr.net/npm/flag-icons@7/css/flag-icons.min.css" rel="stylesheet">
    @yield('layout_css')
    <!-- 字体/Fonts -->
    <link href="/assets/global/fonts/web-icons/web-icons.min.css" rel="stylesheet">
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
        // 初始化国际化管理器
        window.i18n = function(key, fallback) {
            const keys = key.split('.');
            let value = window.i18n.translations || {};

            for (let i = 0; i < keys.length; i++) {
                if (value && typeof value === 'object' && value.hasOwnProperty(keys[i])) {
                    value = value[keys[i]];
                } else {
                    return fallback || key;
                }
            }

            return value || fallback || key;
        };

        // 初始化空的翻译对象
        window.i18n.translations = {};

        // 扩展翻译文本的方法
        window.i18n.extend = function(additionalTranslations) {
            window.i18n.translations = Object.assign({}, window.i18n.translations, additionalTranslations);
        };

        // Create and append link element to load the font CSS asynchronously
        const link = document.createElement("link");
        link.rel = 'stylesheet';
        link.href = 'https://fonts.loli.net/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap';
        document.head.appendChild(link);

        // Apply font to body after font has loaded
        link.onload = function() {
            document.body.style.fontFamily = 'Roboto, system-ui, sans-serif';
        };

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

        function adjustPagination() {
            const paginations = document.querySelectorAll('.pagination');

            paginations.forEach(pagination => {
                const allItems = Array.from(pagination.querySelectorAll('.page-item'));
                // 1. 清理动态省略号并恢复显示以进行测量
                pagination.querySelectorAll('.dynamic-dot').forEach(el => el.remove());
                allItems.forEach(item => item.style.display = '');

                const totalWidthNeeded = pagination.scrollWidth;

                // 2. 寻找第一个宽度限制容器
                let parent = pagination.parentElement;
                while (parent && parent.tagName !== 'HTML' && parent.clientWidth === totalWidthNeeded) {
                    // 如果父容器 clientWidth 明显小于当前 pagination 宽度，说明它就是那个限制框
                    parent = parent.parentElement;
                }
                let limitWidth = parent.clientWidth;

                // 如果宽度足够（给 60px 缓冲），不需要调整
                if (totalWidthNeeded + 60 < limitWidth) return;

                // 3. 更加精准的估算容量
                // 取最后一页的宽度作为基准（通常三位数页码最宽，最具代表性）
                const itemWidth = (allItems[allItems.length - 2] || allItems[0]).offsetWidth || 40;

                // 计算最大可用槽位：(容器宽 / 单个宽) - 预留给省略号的 2 个位置
                let maxSlots = Math.max(5, Math.floor(limitWidth / itemWidth) - 2);

                // 4. 筛选页码（排除 Prev/Next）
                const numItems = allItems.filter(item => {
                    const text = item.textContent.trim();
                    // 排除上一页/下一页图标，只留数字
                    return !isNaN(text) && text !== '' && !item.querySelector('[rel="prev"]') && !item.querySelector('[rel="next"]');
                });

                const activeItem = pagination.querySelector('.active');
                const firstPage = numItems[0];
                const lastPage = numItems[numItems.length - 1];
                const prevBtn = allItems[0];
                const nextBtn = allItems[allItems.length - 1];

                // 5. 构建必须显示的权重集合 (Set)
                let visibleSet = new Set([prevBtn, nextBtn, firstPage, lastPage, activeItem]);

                // 6. 权重填充：按距离 Active 远近填充剩余槽位
                let remaining = maxSlots - visibleSet.size;
                if (remaining > 0) {
                    const activeIdx = allItems.indexOf(activeItem);
                    const sortedNums = [...numItems]
                        .filter(item => !visibleSet.has(item))
                        .sort((a, b) => {
                            const distA = Math.abs(allItems.indexOf(a) - activeIdx);
                            const distB = Math.abs(allItems.indexOf(b) - activeIdx);
                            if (distA === distB) return allItems.indexOf(a) - allItems.indexOf(b); // 距离相等时，左侧优先
                            return distA - distB;
                        });

                    for (let i = 0; i < remaining && i < sortedNums.length; i++) {
                        visibleSet.add(sortedNums[i]);
                    }
                }

                // 7. 执行显示/隐藏
                allItems.forEach(item => {
                    item.style.display = visibleSet.has(item) ? '' : 'none';
                });

                // 8. 补齐省略号 (检查索引断层)
                const finalVisible = allItems.filter(item => item.style.display !== 'none');
                for (let i = 0; i < finalVisible.length - 1; i++) {
                    const currIdx = allItems.indexOf(finalVisible[i]);
                    const nextIdx = allItems.indexOf(finalVisible[i + 1]);

                    if (nextIdx - currIdx > 1) {
                        let nativeDot = null;
                        for (let j = currIdx + 1; j < nextIdx; j++) {
                            if (allItems[j].textContent.includes('...')) {
                                nativeDot = allItems[j];
                                break;
                            }
                        }

                        if (nativeDot) {
                            nativeDot.style.display = '';
                        } else {
                            // 正确插入 HTML 节点的方法
                            finalVisible[i + 1].insertAdjacentHTML('beforebegin',
                                `<li class="page-item disabled dynamic-dot" aria-disabled="true"><span class="page-link">...</span></li>`);
                        }
                    }
                }
            });
        }

        let resizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(adjustPagination, 100);
        });

        // 在 DOM 加载完成后执行一次
        document.addEventListener('DOMContentLoaded', adjustPagination);
    </script>
    @yield('layout_javascript')
</body>

</html>
