@switch(sysConfig('is_captcha'))
    @case(1)
        <!-- Default Captcha -->
        <div class="form-group form-material floating input-group" data-plugin="formMaterial">
            <input type="text" class="form-control" name="captcha" required/>
            <label class="floating-label" for="captcha">{{trans('auth.captcha.attribute')}}</label>
            <img src="{{captcha_src()}}" class="float-right" onclick="this.src='/captcha/default?'+Math.random()" alt="{{trans('auth.captcha.attribute')}}"/>
        </div>
        @break
    @case(2)
        <!-- Geetest -->
        <div class="form-group form-material floating w-p100" data-plugin="formMaterial">
            {!! Geetest::render() !!}
        </div>
        @break
    @case(3)
        <!-- Google reCaptcha -->
        <div class="form-group form-material floating vertical-align-middle mt-20" data-plugin="formMaterial">
            {!! NoCaptcha::display() !!}
            {!! NoCaptcha::renderJs(Session::get('locale')) !!}
        </div>
        @break
    @case(4)
        <!-- hCaptcha -->
        <div class="form-group form-material floating w-p100" data-plugin="formMaterial">
            {!! HCaptcha::display() !!}
            {!! HCaptcha::renderJs(Session::get('locale')) !!}
        </div>
        @break
    @case(5)
        <!-- Turnstile -->
        <div class="form-group form-material floating w-p100" data-plugin="formMaterial">
            {{ \romanzipp\Turnstile\Captcha::getScript() }}
            {{ \romanzipp\Turnstile\Captcha::getChallenge() }}
        </div>
        @break
    @default
@endswitch
