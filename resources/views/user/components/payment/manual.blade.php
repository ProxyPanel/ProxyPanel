@extends('user.layouts')

@section('css')
    <style>
        .tab {
            display: none;
        }

        .hide {
            display: none;
        }

        @media (max-width: 768px) {
            #ad {
                right: 1vw;
                bottom: 20vh;
            }

            #ad img {
                width: 40vw;
            }
        }

        @media (min-width: 768px) {
            #ad {
                right: 3vw;
                bottom: 15vh;
            }

            #ad img {
                width: 30vw;
            }
        }

        @media (min-width: 1200px) {
            #ad {
                right: 10vw;
                bottom: 15vh;
            }

            #ad img {
                width: 20vw;
            }
        }

        #ad {
            position: fixed;
            z-index: 9999;
            margin-right: auto;
        }

        #ad img {
            max-width: 300px;
        }

        #ad > button {
            position: absolute;
            right: 0;
            top: 0;
        }
    </style>
@endsection

@section('content')
    <div id="ad">
        <button class="btn btn-pure btn-outline-light icon wb-close" type="button" onclick="document.getElementById('ad').style.display = 'none'"></button>
        <img src="{{asset('assets/images/help/作者要饭求放过.PNG')}}" alt="支付宝领红包">
    </div>
    <div class="page-content container">
        <div class="panel panel-bordered">
            <div class="panel-heading">
                <h1 class="panel-title cyan-600">
                    <i class="icon wb-payment"></i>{{sysConfig('website_name').' '.trans('common.payment.manual')}}
                </h1>
            </div>
            <div class="panel-body">
                <div class="alert alert-info text-center">
                    <p>扫完二维码，支付完成后。记得回来 点击👇【下一步】 直到 点击👇【{{trans('common.submit')}}】 才算正式支付完成呦！</p>
                </div>
                <div class="steps row w-p100">
                    <div class="step col-lg-4 current">
                        <span class="step-number">1</span>
                        <div class="step-desc">
                            <span class="step-title">须知</span>
                            <p>如何正确使用本支付</p>
                        </div>
                    </div>
                    <div class="step col-lg-4">
                        <span class="step-number">2</span>
                        <div class="step-desc">
                            <span class="step-title">支付</span>
                            <p>获取支付二维码，进行支付</p>
                        </div>
                    </div>
                    <div class="step col-lg-4">
                        <span class="step-number">3</span>
                        <div class="step-desc">
                            <span class="step-title">完成</span>
                            <p>等待支付被确认</p>
                        </div>
                    </div>
                </div>

                <div id="payment-group" class="w-p100 text-center mb-20">
                    <div class="w-md-p50 w-p100 mx-auto btn-group">
                        @if(sysConfig('wechat_qrcode'))
                            <button id="btn-wechat" class="btn btn-lg btn-block" onclick="show(0)">{{trans('common.payment.wechat')}}</button>
                        @endif
                        @if(sysConfig('alipay_qrcode'))
                            <button id="btn-alipay" class="btn mt-0 btn-lg btn-block" onclick="show(1)">{{trans('common.payment.alipay')}}</button>
                        @endif
                    </div>
                </div>
                <div class="tab">
                    <div class="wechat hide">
                        <div class="mx-auto text-center">
                            <h4>备注账号</h4>
                            <img class="w-lg-350 w-md-p50 w-p100 mb-10" src="{{asset('assets/images/help/manual_wechat1.png')}}" alt=""/>
                            <h4>填入登录使用的账号</h4>
                            <img class="w-lg-350 w-md-p50 w-p100 mb-10" src="{{asset('assets/images/help/manual_wechat2.png')}}" alt=""/>
                        </div>
                    </div>
                    <div class="alipay hide">
                        <div class="mx-auto text-center">
                            <h4>备注账号</h4>
                            <img class="w-lg-350 w-md-p50 w-p100 mb-10" src="{{asset('assets/images/help/manual_alipay1.png')}}" alt=""/>
                            <h4>填入登录使用的账号</h4>
                            <img class="w-lg-350 w-md-p50 w-p100 mb-10" src="{{asset('assets/images/help/manual_alipay2.png')}}" alt=""/>
                        </div>
                    </div>
                </div>

                <div class="tab">
                    <div class="wechat hide">
                        <div class="mx-auto text-center">
                            <div class="alert alert-info">
                                {!! trans('user.payment.qrcode_tips', ['software' => trans('common.payment.wechat')]) !!}
                            </div>
                            <img class="w-lg-350 w-md-p50 w-p100 mb-10" src="{{asset(sysConfig('wechat_qrcode'))}}" alt=""/>
                        </div>
                    </div>
                    <div class="alipay hide">
                        <div class="mx-auto text-center">
                            <div class="alert alert-info">
                                {!! trans('user.payment.qrcode_tips', ['software' => trans('common.payment.alipay')]) !!}
                            </div>
                            <img class="w-lg-350 w-md-p50 w-p100 mb-10" src="{{asset(sysConfig('alipay_qrcode'))}}" alt=""/>
                        </div>
                    </div>
                    <div class="alert alert-danger text-center">
                        {!! trans('user.payment.mobile_tips') !!}
                    </div>
                </div>

                <div class="tab">
                    <div class="alert alert-danger text-center">
                        支付时，请充值正确金额（多不退，少要补）
                    </div>
                    <div class="mx-auto w-md-p50 w-lg-p25">
                        <ul class="list-group list-group-dividered">
                            <li class="list-group-item">{{trans('user.shop.service').'：'.$name}}</li>
                            <li class="list-group-item">{{trans('user.shop.price').'：¥'.$payment->amount}}</li>
                            @if($days !== 0)
                                <li class="list-group-item">{{trans('common.available_date').'：'.$days.trans_choice('validation.attributes.day', 1)}}</li>
                            @endif
                        </ul>
                    </div>
                </div>

                <div class="clearfix">
                    <button type="button" class="btn btn-lg btn-default float-left" id="prevBtn" onclick="nextPrev(-1)">上一步</button>
                    <button type="button" class="btn btn-lg btn-primary float-right" id="nextBtn" onclick="nextPrev(1)">下一步</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('javascript')
    <script>
        let currentTab = 0; // Current tab is set to be the first tab (0)
        showTab(currentTab); // Display the current tab
        show({{sysConfig('wechat_qrcode')? 0 : 1}});

        function showTab(n) {
            // This function will display the specified tab of the form ...
            const x = document.getElementsByClassName('tab');
            x[n].style.display = 'block';
            // ... and fix the Previous/Next buttons:
            if (n === 0) {
                document.getElementById('prevBtn').style.display = 'none';
            } else {
                document.getElementById('prevBtn').style.display = 'inline';
            }

            if (n === x.length - 1) {
                document.getElementById('payment-group').style.display = 'none';
                document.getElementById('nextBtn').classList.remove('btn-primary');
                document.getElementById('nextBtn').classList.add('btn-success');
                document.getElementById('nextBtn').innerHTML = '{{trans('common.submit')}}';
            } else {
                document.getElementById('payment-group').style.display = 'inline-flex';
                document.getElementById('nextBtn').innerHTML = '下一步';
                document.getElementById('nextBtn').classList.remove('btn-success');
                document.getElementById('nextBtn').classList.add('btn-primary');
                document.getElementById('nextBtn').style.display = 'inline';
            }

            fixStepIndicator(n);
        }

        function nextPrev(n) {
            // This function will figure out which tab to display
            const x = document.getElementsByClassName('tab');
            // if you have reached the end of the form... :
            if (currentTab === x.length - 1 && n === 1) {
                //...the form gets submitted:
                $.post('{{route('manual.inform', ['payment' => $payment->trade_no])}}', {_token: '{{csrf_token()}}'}, function(ret) {
                    if (ret.status === 'success') {
                        swal.fire({title: '已受理', text: ret.message, icon: 'success'}).then(() => window.location.href = '{{route('invoice')}}');
                    } else {
                        swal.fire({title: ret.message, icon: 'error'}).then(() => window.location.reload());
                    }
                });
                return false;
            } else {
                x[currentTab].style.display = 'none';// Hide the current tab:
                currentTab += n;// Increase or decrease the current tab by 1:
            }

            showTab(currentTab);
        }

        function fixStepIndicator(n) {
            // This function removes the "current" class of all steps...
            let i, x = document.getElementsByClassName('step');
            for (i = 0; i < x.length; i++) {
                x[i].className = x[i].className.replace(' current', ' ');
            }
            //... and adds the "active" class to the current step:
            x[n].className += ' current';
        }

        function show(check) {
            @if(sysConfig('wechat_qrcode'))
            const $wechat = document.getElementsByClassName('wechat');
            const $btn_wechat = document.getElementById('btn-wechat');
            if (check) {
                for (let i = 0; i < $wechat.length; i++) {
                    $wechat[i].style.display = 'none';
                }
                $btn_wechat.classList.remove('btn-success');
            } else {
                for (let i = 0; i < $wechat.length; i++) {
                    $wechat[i].style.display = 'inline';
                }
                $btn_wechat.classList.add('btn-success');
            }
            @endif
            @if(sysConfig('alipay_qrcode'))
            const $alipay = document.getElementsByClassName('alipay');
            const $btn_alipay = document.getElementById('btn-alipay');
            if (check) {
                for (let i = 0; i < $alipay.length; i++) {
                    $alipay[i].style.display = 'inline';
                }
                $btn_alipay.classList.add('btn-primary');
            } else {
                for (let i = 0; i < $alipay.length; i++) {
                    $alipay[i].style.display = 'none';
                }
                $btn_alipay.classList.remove('btn-primary');
            }
            @endif
        }
    </script>
@endsection
