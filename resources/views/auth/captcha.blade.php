@switch(sysConfig('is_captcha'))
    @case(1)
        <!-- Default Captcha -->
        <div class="form-group form-material floating input-group" data-plugin="formMaterial">
            <input class="form-control" name="captcha" type="text" required />
            <label class="floating-label" for="captcha">{{ trans('auth.captcha.attribute') }}</label>
            <img class="float-right" src="{{ captcha_src() }}" alt="{{ trans('auth.captcha.attribute') }}" onclick="this.src='/captcha/default?'+Math.random()" />
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
