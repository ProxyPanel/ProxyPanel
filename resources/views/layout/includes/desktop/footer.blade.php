<!-- FOOTER VERSION -->
<footer class="page-footer">
            <div class="container">
                <div class="page-footer__col">
                    <h4 class="col-title">{{ __('static.dsktp_section_footer_title_1') }}</h4>
                    <ul class="client-platforms">
                        <li>
                            <a href="{{ url('/vpn-apps') }}">
                                <img src="{{ asset('assets/static/desktop/images/logos/windows-footer-logo.svg') }}" alt="windows">
                                <span>Windows</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ url('/vpn-apps') }}">
                                <img src="{{ asset('assets/static/desktop/images/logos/mac-footer-logo.png') }}" alt="mac">
                                <span>Mac</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ url('/vpn-apps') }}">
                                <img src="{{ asset('assets/static/desktop/images/logos/apple-footer-logo.svg') }}" alt="ios">
                                <span>iOS</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ url('/vpn-apps') }}">
                                <img src="{{ asset('assets/static/desktop/images/logos/android-footer-logo.svg') }}" alt="android">
                                <span>Android</span>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="page-footer__col">
                    <h4 class="col-title">{{ __('static.dsktp_section_footer_title_2') }}</h4>
                    <ul class="footer-menu">
                        <li><a href="{{url('contact')}}">{{ __('static.contact') }}</a></li>
                        <li><a href="{{url('tutorial')}}">{{ __('static.tutorial') }}</a></li>
                        
                        <li><a href="{{url('term')}}">{{ __('static.term') }}</a></li>
                       <li><a href="{{url('help-n')}}">{{ __('static.help') }}</a></li>
                    </ul>
                </div>
                <div class="page-footer__col">
                    <h4 class="col-title">{{ __('static.dsktp_section_footer_title_3') }}</h4>
                    <ul class="social-list">
                        <li><a href="#"><i class="fab fa-facebook-f"></i></a></li>
                        <li><a href="#"><i class="fab fa-twitter"></i></a></li>
                        <li><a href="#"><i class="fab fa-instagram"></i></a></li>
                        <li><a href="#"><i class="fab fa-youtube"></i></a></li>
                    </ul>
                </div>
            </div>
        </footer>

        <!-- FOOTER FOR HELP PAGE -->
        @if (Request::segment(2) == 'subpage')
        <!-- <footer class="page-footer--help">
            <div class="container">
                <div class="row">
                    <div class="page-footer--help__brand">
                        <h4>RitaVPN</h4>
                    </div>
                    <div class="page-footer--help__menu">
                        <h4 class="col-title">Shortcut</h4>
                        <ul class="footer-menu">
                            <li><a href="#">Home</a></li>
                            <li><a href="#">Price</a></li>
                            <li><a href="#">VPN Downloads</a></li>
                            <li><a href="#">Contact Us</a></li>
                            <li><a href="#">VPN Setup Tutorial</a></li>
                            <li><a href="#">Help</a></li>
                            <li><a href="#">Term of use</a></li>
                            <li><a href="#">About us</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </footer> -->
        @endif

    </div> <!-- ./end wrappers -->

<div class="jump-page-top" aria-label="Jump up to top of the page"><i class="fa fa-arrow-up" aria-hidden="true"></i>
</div>

<!-- Signin Modal -->
<div class="modal fade" id="signinModal" tabindex="-1" role="dialog" aria-labelledby="signinModal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>

            <div class="modal-body">

                <div class="auth-content">
                    <div class="modal-tab">
                        <span>Sign in</span>
                    </div>

                     @if(Session::get('errorLoginMsg'))
                        <div class="alert alert-danger">
                            <!-- <button class="close" data-close="alert"></button> -->
                            <span> {{Session::get('errorLoginMsg')}} </span>
                        </div>
                    @endif
                   
                    <form action="{{url('login')}}" class="form--signin" method="post">
                        <div class="md-form">
                            <i class="icon-user"></i>
                            <input type="text" id="email" class="form-control" autocomplete="off" placeholder="{{__('login.username')}}" name="email" value="{{Request::old('username')}}" required>
                        </div>
                        <div class="md-form">
                            <i class="icon-lock"></i>
                            <input type="password" id="pass" class="form-control" autocomplete="off" placeholder="{{__('login.password')}}" name="password" value="{{Request::old('password')}}" required>
                        </div>
                        <input type="hidden" name="_token" value="{{csrf_token()}}" />

                        <button  type="submit" class="cs-btn cs-btn--primary">Sign in</button>

                        <div class="form-bottom">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" name="remember" value="1" id="signin_check">
                                <label class="custom-control-label" for="signin_check"><span>Auto Login</span></label>
                            </div>
                            <a href="javascript:void(0)" class="modal-trigger" data-modal="#resetpassModal">Password Reset</a>
                        </div>
                      
                        @if(sysConfig('is_register'))
                        <div class="form-link text-center">
                            <a href="javascript:void(0)" class="modal-trigger" data-modal="#signupModal">SignUp</a>
                        </div>
                        @endif
                        
                      
                       
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Signup Modal -->
<div class="modal fade" id="signupModal" tabindex="-1" role="dialog" aria-labelledby="signupModal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>

            <div class="modal-body">
                <div class="auth-content">

                    <div class="modal-tab">
                        <span>Sign Up</span>
                    </div>
                    @if(Session::get('errorRegMsg'))
                        <div class="alert alert-danger">
                            <!-- <button class="close" data-close="alert"></button> -->
                            <span> {{Session::get('errorRegMsg')}} </span>
                        </div>
                    @endif


                    <form action="{{url('register')}}" method="post" class="form--signup">
                        <input type="hidden" name="register_token" value="{{Session::get('register_token')}}" />
                        <input type="hidden" name="_token" value="{{csrf_token()}}" />
                        <input type="hidden" name="aff" value="{{Session::get('register_aff')}}" />

                        <div class="md-form">
                            <i class="icon-user"></i>
                            <input type="text" id="signup_email" class="form-control" autocomplete="off" placeholder="{{__('register.username_placeholder')}}" name="username" value="{{Request::old('username')}}" required>
                        </div>
                        <div class="md-form">
                            <i class="icon-lock"></i>
                            <input type="password" id="signup_pass" class="form-control"  autocomplete="off" placeholder="{{__('register.password')}}" name="password" value="{{Request::old('password')}}" required>
                        </div>
                        <div class="md-form">
                            <i class="icon-lock"></i>
                            <input type="password" id="signup_pass_2" class="form-control"  autocomplete="off" placeholder="{{__('register.retype_password')}}" name="repassword" value="{{Request::old('repassword')}}" required>
                        </div>

                        @if(sysConfig('is_register'))
                            <button type="submit" class="cs-btn cs-btn--primary">Sign Up</button>
                        @endif

                        <div class="form-bottom">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="signup_check">
                                <label class="custom-control-label" for="signup_check"><span>Login means you agree <a href="#">terms of servcie</a></span></label>
                            </div>
                        </div>

                        <div class="form-link text-center">
                            <a href="javascript:void(0)" class="modal-trigger" data-modal="#signinModal">Already have an account? Log in.</a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Password reset Modal -->
<div class="modal fade" id="resetpassModal" tabindex="-1" role="dialog" aria-labelledby="resetpassModal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>

            <div class="modal-body">
                <div class="auth-content">

                    <div class="modal-tab">
                        <span>Password Reset</span>
                    </div>

                    <form action="{{url('resetPassword')}}" class="form--reset" method="post">
                        @if(sysConfig('is_reset_password'))
                        <div class="md-form">
                            <i class="icon-user"></i>
                            <input type="text" id="reset_email" class="form-control" autocomplete="off" placeholder="{{__('home.username_placeholder')}}" name="username" value="{{Request::old('username')}}" required autofocus >
                            <input type="hidden" name="_token" value="{{csrf_token()}}" />
                        </div>
                        <button type="submit" class="cs-btn cs-btn--primary">Send</button>
                        @else
                            <div class="alert alert-danger">
                                <span> {{__('home.system_down')}} </span>
                            </div>
                        @endif
                        <div class="form-link text-center">
                            <a href="javascript:void(0)" class="modal-trigger" data-modal="#signinModal">Sign in</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@yield("footer-script")

<!-- <script src="{{ asset('assets/static/desktop/js/login.js') }}"></script>  -->

<script>
    $(function() {

        $.each($('.modal'), function() {
            if ($(this).find('.alert').length) {
                $(this).modal('show');
            }
        });

        $('.form--signup').on('submit', function(e) {
            e.preventDefault();
            e.stopPropagation();

            let checkbox = $('#signup_check');

            if (!checkbox.is(':checked')) {
                checkbox.next('label').addClass('has-error');
            } else {
                $('.form--signup')[0].submit();
                return;
            }

        });
    });
</script>

</body>

</html>
