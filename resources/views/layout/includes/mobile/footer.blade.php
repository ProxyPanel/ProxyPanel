<footer class="page-footer">
    <div class="container">

        <div class="client-platforms">
            <a class="cs-btn cs-btn--primary cs-btn--ios" style="display: none">
                <img src="{{ asset('assets/static/mobile/images/index/apple-store-graphics.png') }}" alt="apple download">
            </a>
            <a class="cs-btn cs-btn--primary cs-btn--android" style="display: none">
                <img src="{{ asset('assets/static/mobile/images/index/playe-store-graphics.png') }}" alt="android download">
            </a>
        </div>

        <div class="page-footer__col page-footer__col--shortcut">
            <h4 class="col-title">{{ __('static.mbl_section_footer_title_1') }}</h4>
            <ul class="footer-menu">
                <li><a href="#">Contact Us</a></li>
                <li><a href="#">VPN Setup Tutorial</a></li>
                <li><a href="#">Help</a></li>
                <li><a href="#">Term of use</a></li>
                <li><a href="#">About us</a></li>
            </ul>
        </div>
        <div class="page-footer__col page-footer__col--lang">
            <h4 class="col-title">{{ __('static.mbl_section_footer_title_2') }}</h4>
            <div class="select-wrapper">
                <select class="js-footer-lang">
                    <option value="en" data-value="en" {{  app()->getLocale() === 'en' ? 'selected' : '' }}>English</option>
                    <option value="ch" data-value="ch" {{  app()->getLocale() === 'zh-CN' ? 'selected' : '' }}>Chinese</option>
                </select>
            </div>
        </div>
        <div class="page-footer__col">
            <h4 class="col-title">{{ __('static.mbl_section_footer_title_3') }}</h4>
            <ul class="social-list">
                <li><a href="#"><i class="fab fa-facebook-f"></i></a></li>
                <li><a href="#"><i class="fab fa-twitter"></i></a></li>
                <li><a href="#"><i class="fab fa-instagram"></i></a></li>
                <li><a href="#"><i class="fab fa-youtube"></i></a></li>
            </ul>
        </div>
    </div>
</footer>

</div> <!-- ./end wrappers -->

@yield("footer-script")

<script>
    $(function() {
        $('.js-footer-lang').on('change', function(e) {
            let value = e.target.value;
            if (value === 'en') {
                window.location.replace(window.location.origin + '/' + 'locale/en');
            } else {
                window.location.replace(window.location.origin + '/' + 'locale/zh-CN');
            }
        });
        function getMobileOperatingSystem() {
            var userAgent = navigator.userAgent || navigator.vendor || window.opera;
            if (/android/i.test(userAgent)) {
                return "Android";
            }
            // iOS detection from: http://stackoverflow.com/a/9039885/177710
            if (/iPad|iPhone|iPod/.test(userAgent) && !window.MSStream) {
                return "iOS";
            }
            return "unknown";
        }
        let mobileOperation = getMobileOperatingSystem();
        if (mobileOperation === "Android") {
            $(".cs-btn--android").show();
        } else if (mobileOperation === "iOS") {
            $(".cs-btn--ios").show();
        } else {
            $(".cs-btn--ios").show();
            $(".cs-btn--android").show();
        }
    });
</script>

</body>

</html>