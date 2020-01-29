@extends('auth.layouts')
@section('title', trans('auth.register'))
@section('css')
	<link href="/assets/global/vendor/bootstrap-select/bootstrap-select.min.css" type="text/css" rel="stylesheet">
	<link href="/assets/custom/Plugin/sweetalert2/sweetalert2.min.css" type="text/css" rel="stylesheet">
@endsection
@section('content')
	<form action="/register" method="post" id="register-form">
		{{csrf_field()}}
		<input type="hidden" name="register_token" value="{{Session::get('register_token')}}"/>
		<input type="hidden" name="aff" value="{{Session::get('register_aff')}}"/>
		@if(\App\Components\Helpers::systemConfig()['is_register'])
			@if($errors->any())
				<div class="alert alert-danger">
					<span>{{$errors->first()}}</span>
				</div>
			@endif
			<div class="form-group form-material floating" data-plugin="formMaterial">
				@if(\App\Components\Helpers::systemConfig()['sensitiveType'])
					<input type="email" class="form-control" autocomplete="off" name="username" value="{{Request::old('username')}}" id="username" required/>
					<label class="floating-label" for="username">{{trans('auth.username')}}</label>
				@else
					<div class="input-group">
						<input type="text" class="form-control" autocomplete="off" name="emailHead" value="{{Request::old('emailHead')}}" id="emailHead" required/>
						<label class="floating-label" for="emailHead">{{trans('auth.username')}}</label>
						<div class="input-group-prepend">
							<span class="input-group-text bg-indigo-600 text-white">@</span>
						</div>
						<select class="form-control" name="emailTail" id="emailTail" data-plugin="selectpicker" data-style="btn-outline-primary">
							@if(!$emailList->isEmpty())
								@foreach($emailList as $email)
									<option value="{{$email->words}}">{{$email->words}}</option>
								@endforeach
							@endif
						</select>
						<input type="text" name="username" id="username" hidden/>
					</div>
				@endif
			</div>
			@if(\App\Components\Helpers::systemConfig()['is_verify_register'])
				<div class="form-group form-material floating" data-plugin="formMaterial">
					<div class="input-group" data-plugin="inputGroupFile">
						<input type="text" class="form-control" name="verify_code" value="{{Request::old('verify_code')}}" required/>
						<label class="floating-label" for="verify_code">{{trans('auth.captcha')}}</label>
						<span class="input-group-btn">
                            <span class="btn btn-success" id="sendCode" onclick="sendVerifyCode()">
                                {{trans('auth.request')}}
                            </span>
                        </span>
					</div>
				</div>
			@endif
			<div class="form-group form-material floating" data-plugin="formMaterial">
				<input type="password" class="form-control" autocomplete="off" name="password" required/>
				<label class="floating-label" for="password">{{trans('auth.password')}}</label>
			</div>
			<div class="form-group form-material floating" data-plugin="formMaterial">
				<input type="password" class="form-control" autocomplete="off" name="confirmPassword" required/>
				<label class="floating-label" for="confirmPassword">{{trans('auth.confirm_password')}}</label>
			</div>
			@if(\App\Components\Helpers::systemConfig()['is_invite_register'])
				<div class="form-group form-material floating" data-plugin="formMaterial">
					<input type="password" class="form-control" name="code" value="{{Request::old('code') ? : Request::get('code')}}" @if(\App\Components\Helpers::systemConfig()['is_invite_register'] == 2) required @endif/>
					<label class="floating-label" for="code">{{trans('auth.code')}}</label>
				</div>
				@if(\App\Components\Helpers::systemConfig()['is_free_code'])
					<p class="hint">
						<a href="/free" target="_blank">{{trans('auth.get_free_code')}}</a>
					</p>
				@endif
			@endif
			@switch(\App\Components\Helpers::systemConfig()['is_captcha'])
				@case(1)<!-- Default Captcha -->
				<div class="form-group form-material floating input-group" data-plugin="formMaterial">
					<input type="text" class="form-control" name="captcha" required/>
					<label class="floating-label" for="captcha">{{trans('auth.captcha')}}</label>
					<img src="{{captcha_src()}}" class="float-right" onclick="this.src='/captcha/default?'+Math.random()" alt="{{trans('auth.captcha')}}"/>
				</div>
				@break
				@case(2)<!-- Geetest -->
				<div class="form-group form-material floating" data-plugin="formMaterial">
					{!! Geetest::render() !!}
				</div>
				@break
				@case(3)<!-- Google noCAPTCHA -->
				<div class="form-group form-material floating" data-plugin="formMaterial">
					{!! NoCaptcha::display() !!}
					{!! NoCaptcha::renderJs(session::get('locale')) !!}
				</div>
				@break
				@default
			@endswitch
			<div class="form-group mt-20 mb-20">
				<div class="checkbox-custom checkbox-primary">
					<input type="checkbox" name="term" id="term" {{Request::old('term') ? 'checked':''}} />
					<label for="term">{{trans('auth.accept_term')}}
						<button class="btn btn-xs btn-primary" data-target="#tos" data-toggle="modal" type="button">{{trans('auth.tos')}}</button>
						&
						<button class="btn btn-xs btn-primary" data-target="#aup" data-toggle="modal" type="button">{{trans('auth.aup')}}</button>
					</label>
				</div>
			</div>
		@else
			<div class="alert alert-danger">
                <span>
                    {{trans('auth.system_maintenance')}}
                </span>
			</div>
		@endif
		<a href="/login" class="btn btn-danger btn-lg {{\App\Components\Helpers::systemConfig()['is_register']? 'float-left': 'btn-block'}}">{{trans('auth.back')}}</a>
		@if(\App\Components\Helpers::systemConfig()['is_register'])
			<button type="submit" class="btn btn-primary btn-lg float-right">{{trans('auth.register')}}</button>
		@endif
	</form>
@endsection
@section('modal')
	<div class="modal fade modal-info text-left" id="tos" aria-hidden="true" aria-labelledby="tos" role="dialog" tabindex="-1">
		<div class="modal-dialog modal-simple modal-sidebar modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close mr-15" data-dismiss="modal" aria-label="Close" style="position:absolute;">
						<span aria-hidden="true">×</span>
					</button>
					<h4 class="modal-title">{{\App\Components\Helpers::systemConfig()['website_name']}} - {{trans('auth.tos')}} <small>2019年11月28日10:49</small></h4>
				</div>
				<div class="modal-body">
					<h2>当事双方及术语介绍</h2>
					本协议所提及的当事人的定义如下：
					<ol>
						<li>就本协议中，提及的{{\App\Components\Helpers::systemConfig()['website_name']}}相关网站或数据传输服务，均简称为“本站”或“服务”。 当第一人称代词（我们等）在以下协议中使用，均指{{\App\Components\Helpers::systemConfig()['website_name']}}。
						<li>您，本站客户 - 作为我们的客户或网站服务的使用者， 本协议可能使用任意第二人称代替。</li>
					</ol>

					请认真阅读下面的服务条款和条件。通过购买或注册本站服务，即表示您同意遵守本协议的所有条款和条件（协议）。如果同意本协议的条款和条件，请将选择框勾上，即表示你接受这些条款和条件。您可以通过打印或以其他方式保存本协议，作为未来可引用的副本。如果您不同意此服务和条款，请勿购买以及使用本站的任意产品和服务，并点击浏览器“返回”按钮，或关闭本网站。{{\App\Components\Helpers::systemConfig()['website_name']}}仅在您同意此服务条款后，为您提供服务。此协议是在全球和国内的商业法允许下签署。任何使用{{\App\Components\Helpers::systemConfig()['website_name']}}提供的服务的行为，都认为您知晓并同意以下服务条款。
					<h2>使用条款</h2>
					尽管此协议代表主要的使用条款，其他准则和规则在此通过引用并入本文。这些文件可以在我们网站上找到，其中包含：
					<h3>一丶服务</h3>
					<ol>
						<li>在您选购本站的服务之前，您可以看到一系列罗列出的套餐。您可以从罗列的服务中选择所需套餐。所有您选择的服务，均适用此条款。 在您订购服务后，我们认为您接受此使用条款，本站会在购买后自动开启服务。本站保留以任何原因拒绝为您提供任何服务的权利。本站也有权在紧急状况中断接入服务进行定期维护需要。您可以随时订购额外的服务。所有的额外服务在下文被视为“服务”。所提供的所有服务都视情况而定，并适用于本协议的所有条款和条件。</li>
						<li>本站服务只限定购买者本人使用以及其家庭成员之间共享使用，除此之外禁止以任何形式分享使用。为了避免本站账户泄漏或者用户主动分享，本站有权在用户不知情情况下对违规账号进行删除或者封禁处理。</li>
					</ol>
					<h3>二丶用户协议修订</h3>
					本协议包含完整的条款和条件适用于您使用本站服务（定义如下）。本站可以在任何时间修改本协议的条款和费用（定义见下文）。
					<ol>
						<li>本站可能随时修改本协议，您同意本站保留修改本协议的权利。您同意我们有单方面这样做的权利。更新的版本将在发布后立即取代任何以往的版本，之前的版本是不再具备任何法律效力，除非修订版和之前版本一致。本协议的任何第三方修改版本均被视为无效。</li>
						<li>本站同意，如果我们协议有任何改变，我们将在本协议的顶部更改“最后修改日期”。您同意定期重新访问该网页。您同意注意最后修订本协议的日期。如果“上次修改”日期仍从你最后一次查看本协议不变，那么你可以假定该协定已自上次你查看时它已经改变了。如果“上次修改”日期已经改变，那么你可以肯定协议中有关条款已被更改。</li>
						<li>如果由于本站所做的任何修改，您需要终止本协议，必须于上述的“最后修改”日起三十（30）日内通过工单申请或者邮箱申请（<a href="mailto:{{\App\Components\Helpers::systemConfig()['webmaster_email']}}">{{\App\Components\Helpers::systemConfig()['webmaster_email']}}</a>）取消。在任何此类通知的生效日期后继续使用服务，即表示您接受这些修改。</li>
						<li>弃权 – 如果您没有定期查看此协议, 您自行承担忽略此协定更改的责任。您拥有随时查看此修订版本的权利。由于您自己忽略查看的原因，本站不负任何责任。</li>
					</ol>
					<h3>三丶协议和取消政策期限</h3>
					<ol>
						<li>初始期限开始于确认你的订单或服务，并收到合法的资金的时间。这个期限的长度是由您选择的，并且在您订购本站服务时应注明。本协议不得终止您的初始期间，除非本站违约。</li>
						<li>本站也可以在任何时间以任何理由或无理由终止本协议 （“无故终止”）。在这种情况下，本站会为您提供工单通知。</li>
						<li>如果本站根据本协议任何条款概述取消本协议，没有根据本协议第3.2条款终止的原因外，本站不会给予退款。若您需要终止服务，您有义务在预付费服务生效前取消订阅。在由本站发起的终止情况，所有预付托管费将被没收，概不退还。本协议的终止并不解除支付任何费用。</li>
						<li>
							除此之外，本站拥有终止此协议中任意部分的权利。当以下情况发生，本站可以终止你的服务：
							<ol type="a">
								<li>违反了可接受使用条款（“AUP”）中的任意条款，</li>
								<li>侵犯或违反任何第三方的知识产权或隐私权或版权，</li>
								<li>不遵守任何适用的法律、法规或条例，或</li>
								<li>已经上传，发布或传播任何本站认为违法或高风险的图像、文本、图形、编码或视频，在其自由裁量权。本协议中的任何内容的目的是，本站不负有任何责任或义务来监视或审查您的内容，或在任何时候你的用户上传或发布的内容。你仍然对你的内容负责，以及对由此产生的任何责任负责。</li>
							</ol>
						</li>
						<li>本协议的终止将终止你访问你的服务和您的许可的托管材料（本协议5.2节所定义）。本站不需要您或任何第三方的允许即可终止你的服务。本协议终止时，本站有权维护备份您的数据文件和记录的副本，但不承担任何义务为您备份。本站保留在所有服务结算周期的最后一天之前实行提前终止的权利。</li>
						<li>如果任何一方以任何原因取消或终止本协议，您将全权负责所有必需的安排。</li>
					</ol>
					<h3>四丶账户设置</h3>
					<ol>
						<li>注册账户时，您将被要求填写登录账户（电子邮件）和密码。您可以并只可以通过这样的用户标识和密码使用该服务或修改您的数据和内容。您完全负责维护您的用户名和密码的保密性和使用这些凭据的所有活动的机密性。您同意立即通知我们当任何未经授权的访问使用您的帐户或任何其他违反安全的情况。
						</li>
						<li>您必须为我们提供一个主要的电子邮件地址，定期检查。所有通知和我们之间的通信将被发送到您提供的电子邮件地址，因此，您需要保持这个地址畅通，如果您的地址更改，请通知我们。如果您的联系和/或帐单信息发生变化，您应该通知我们，以便我们可以更新您的帐户。它确保我们的域名不包括在由你或你的邮件提供商使用任何垃圾邮件阻止列表之内。</li>
						<li>提供任何种类的错误或不准确的联系信息，依据本协议3中相关条款，可能会导致你的帐户被终止。</li>
					</ol>
					<h3>五丶知识产权</h3>
					本站提供的所有服务仅允许用于合法目的。
					<ol>
						<li>在你和本站之间，本站承声明它不拥有你提供的使用在你的网站的所有权或内容（包括但不限于文字、软件、音乐、声音、视听作品、电影、图片、动画、视频和图形）（你的内容）。您特此授予本站使用你的内容，通过互联网广播传输非独家的、世界范围的、免版税许可、复制、制作衍生作品、展示、表演。</li>
						<li>本站可以（但不是必须）为你提供一定的材料，包括但不限于计算机软件（目标代码或源代码形式）、数据文件或信息开发、本站或其供应商提供本协议项下的域名、电子邮件地址、分配给你的其他网络地址和其他专有技术、方法、设备和工艺，采用本站为您提供服务（“主材料”）。根据本协议的条款和条件，本站授予您与服务连接单独使用的主体材料有限的、可撤销、不可转让的、非排他性的许可。本协议终止时，该许可证终止。您承认并同意，本站拥有一切权利，所有权和利益，或以其他方式取得的主体材料的所有适用的许可证、所有的著作权、商业秘密、专利、商标和其他知识产权的权利。任何在本协议终止后的主机材料未经许可，严禁使用。您同意没有获得书面许可不会上传、传输、复制、分发或以任何方式利用任何主机材料。</li>
						<li>本协议不构成许可使用本站服务标志或任何其他贸易徽章。没有本站事先书面同意的使用任意绅士本站服务标志或任何其他贸易标志是严格禁止的。</li>
						<li>你知道，即使是名义的损害，也可能需要大量的法律费用，旅行费用，成本和其他金额。你同意你将支付所有这些费用和费用</li>
					</ol>
					<h3>六丶内容及可接受使用政策</h3>
					<ol>
						<li>您同意遵守本站的使用政策（AUP，即“ 可接受使用策略”），它可以通过访问的本页同目录下找到。其中部分并入本文作为参考并作为本文不可或缺的一部分。本站在网站上张贴修改后的政策，并保留在任何时间修改可接受使用策略的权力。您同意定期访问本站网站并查看最新的可接受使用策略，在任何可接受使用策略更改日志后继续使用本站服务，则代表您接受新的可接受使用策略并受到它的约束。如果最终用户的行为违反服务条款或者可接受使用策略，本站将有权在任何时间中止您对服务的访问。</li>
						<li>本站不会主动侦测最终用户在本站服务上所使用的内容，虽然本站可以自行决定，以技术手段监测客户在本站网络上所使用的服务，并在法律、法规或政府组织要求的情况下透露您账户的任何必要信息。本站将调查侵犯第三方权利或违反可接受使用策略的投诉。本站将试图减少对本站服务的滥用。本站将有权与执法机关合作，并保留通知该机关的权力，如果执法机关怀疑您和您的最终用户进行违反的您和服务器所在地区法律、法规和相关政策的活动。本节包含的所有条款，是为了授予第三方权利，但没有第三方有权强制执行本协议的任何条款。</li>
						<li>您必须同意本站将不承担您和您的最终用户任何违反可接受使用策略和您和服务器所在地区法律、法规和相关政策的行为，这包括数字千年版权法。</li>
						<li>本站可自行决定终止您对服务的访问，并终止本协议。这是因为您或您的最终用户、下游客户违反了服务条款和可接受使用策略。</li>
						<li>本站对于儿童色情问题非常重视，未成年人使用我们的服务存在潜在的危害，这应当是完全禁止的，然而，其监护人可以授权未成年人有限制地使用本站的服务。任何可能被认为是儿童色情的内容都将被删除并禁止访问，这适用于本站的云主机和云加速服务。任何通过本站访问儿童色情的客户都将被立即删除服务并通知当地执法机关。您同意和本站合作阻止儿童色情内容的的访问，任何儿童色情内容，或招揽、引诱或诱使未成年人的性活动或者猥亵行为也是严格禁止的，并将被视为同儿童色情相同问题。本站有权对在使用本站服务访问儿童色情内容的客户提起诉讼。</li>
						<li>如果您怀疑本站网络被托管了儿童色情内容，我们鼓励您立即向本站的滥用投诉邮箱 <a href="mailto:{{\App\Components\Helpers::systemConfig()['webmaster_email']}}">{{\App\Components\Helpers::systemConfig()['webmaster_email']}}</a> )或者通过客户中心的工单系统向滥用投诉部门投诉，并包括客户的文件名或URL（或其他位置点）、受害者（如果知道的话）、出生日期、生产日期以及有关可疑图像（多个）和其他任何信息。或者，您可以使用CyberTipline报告可疑的儿童色情制品，涉及不由本站托管的儿童色情内容应该直接向执法部门或者该网站投诉：<a href="https://www.asacp.org/index.php?content=report" target="_blank">https://www.asacp.org/index.php?content=report</a>。</li>
						<li>我们尊重各方的知识产权，并已通过了关于基于数字千年版权法案重复版权侵权者终止政策。我们的重复侵权终止政策的副本可应要求提供给我们的客户。</li>
						<li>您同意您有责任防止您照顾的未成年人从您购买的本站服务直接或间接访问任何有害或不当的内容。您同意不允许未成年人访问任何服务，并采取相关的限制措施，以防止他们这样做。许多商业在线安全过滤器可这可以帮助用户限制未成年人有害的或不适当的访问请注意，本网站不作任何陈述或有关的任何产品或在这些网站上引用的服务的保证，推荐用户购买或安装任何在线过滤器前进行适当的尽职调查。您同意采取特别措施，防止未成年人浏览本网站如果您的计算机可以由未成年人进行访问。最后，您同意，如果你是父母或未成年子女的监护人，您有责任阻止未成年人通过本站服务访问任何不当内容，这是你的责任，不是我们的。</li>
					</ol>
					<h3>七丶垃圾邮件零容忍政策</h3>
					<ol>
						<li>对于任何垃圾邮件在本站网络上的使用是严格禁止的，如果您和您的最终用户在本站网络上使用SPAM，我们有权随时终止您的服务。</li>
						<li>本站在网站上张贴修改后的政策，并保留在任何时间修改反垃圾邮件政策的权力。您同意定期访问本站网站并查看最新的反垃圾邮件政策，在任何反垃圾邮件政策更改日志后继续使用本站服务，则代表您接受新的反垃圾邮件政策并受到它的约束。如果最终用户的行为违反服务条款或者反垃圾邮件政策，本站将有权在任何时间中止您对服务的访问。</li>
					</ol>
					<h3>八丶支付及退款</h3>
					<p>您同意支付任何由于使用本服务而造成的税款，包括个人所得税、增值税或销售税。本站不负责由于您使用本服务并使用银行支票、信用卡、资金不足以及任何您与您的金融机构产生的任何费用。本站应该得到全额付款，如果由于税收、汇率差异、银行收费、转账收费等产生额外费用，您需要自行支付。</p>
					<p>一旦本站开始提供服务，我们就将不再受理任何退款请求，除非这是因为本站产品和服务存在广泛的，通用的并可被复现的质量问题或是在您没有出现违规使用行为的前提下，本站主动终止了您的服务。否则任何退款请求都将是不被接受的。您需要了解并同意，即使您请求取消了已购买的服务或产品，但是本站已经消耗了基础的成本并承担了风险，因此我们不会退还任何费用。</p>
					<h3>九丶资源使用&安全</h3>
					<p>本站没有对每个账户可使用的系统资源及硬件设限。我们不主动地停用用户帐户，除非它们大大超过可接受的使用水平，或者维持该客户的使用会严重影响其他客户的体验等。</p>
					<p>除非法律明确允许，不得反向工程，反编译，反汇编本网站和/或材料的衍生作品。您同意不使用任何自动设备或手动过程监控或复制本网站或材料，不会使用任何设备，软件，计算机代码或病毒干扰或企图干扰或破坏我们的服务和网站。</p>
					<p>安全 - 任何违反网站和/或服务的安全都是被禁止的，并可能导致刑事和民事责任。您同意不以此类活动违反或企图改变或操纵硬件和软件，危及服务器，或任何其他未经授权的使用。您被禁止从事：</p>
					<ol>
						<li>任何形式的未经授权的访问或使用数据，系统或网络，包括网站和/或服务。</li>
						<li>对任何用户的服务，主机或网络进行未经授权的干扰。</li>
						<li>将恶意程序引入网络或服务器（例如病毒和蠕虫），包括网站和/或服务。</li>
						<li>规避任何主机，网络或账户的用户和安全认证。</li>
						<li>利用我们的服务危及安全或其它网站。</li>
					</ol>
					<p>在您参与任何违反系统安全的情况下，我们有权为其他网站的系统管理员发布关于您的信息，以协助解决安全事件，我们也将与任何执法机构调查犯罪及违反系统或网络的安全性。此外，这些安全规定的违反可能导致您的帐户被禁用。</p>
					<p>流量使用 - 您每月的数据传输量是由您购买特定的服务或订阅来决定。如果您的使用超过您的每月限额，您的服务可能被暂停，并根据不同的产品或服务在下个月月初或您的账单日恢复。未使用的流量不结转到下个月的流量。</p>
					<p>公平使用政策 - 我们提供特定的服务或订阅给我们的客户，我们期望用户按照描述来使用每一项服务，这是指整个任何给定的计费周期来定义正常，公平，合理的使用。当我们判断某一个用户不合理使用服务或订阅，我们可能会采取行动，以减轻负面影响，这些手段包括但不限于连接限制和访问限制。不公平的使用包括但不限于以下内容：</p>
					<h4>违规行为定义</h4>
					<ol>
						<li>
							一般违规行为定义
							<ol>
								<li>在网络节点上进行违反该节点所在国家或地区相关法律法规的行为。</li>
								<li>进行P2P下载，包括但不限于BT、PT、迅雷等。</li>
								<li>长时间占用超过合理范围内的资源以至于影响其他用户的使用行为。（占用带宽50M以上，连续占用时间超过1个小时）</li>
								<li>在游戏专用节点上进行大带宽使用行为，包括但不限于在线视频、文件上传下载、非游戏相关文件传输等。</li>
							</ol>

						</li>
						<li>
							严重违规行为定义
							<ol>
								<li>转借、泄露、二次销售账户或服务信息。</li>
								<li>未提前告知的商业团队使用行为。</li>
							</ol>
						</li>
					</ol>
					<h4>处理办法</h4>
					<ol>
						<li>针对一般违规行为，首次触发，封禁账户7天，第二次触发，封禁账户至服务期结束，并拒绝后续一切服务请求。</li>
						<li>针对严重违规行为，一旦触发，封禁账户至服务期结束，并拒绝后续一切服务请求。</li>
					</ol>
					<h3>十丶正常运行时间保障 </h3>
					<p>本站尽力保证服务可用性。当遇到不可抗拒因素，软件故障、DDOS攻击等问题导致服务中断，本站会尽速恢复基础服务的运行，但不能保障完全恢复。您理解并同意，当您遇到可用性问题时，会通知本站进行处理。</p>
					<h3>十一丶价格变化</h3>
					<p>您支付的服务在一定时间内不会改变价格。我们保留在任何时候更改服务价格的权利，恕不另行通知，并保留修改提供给用户的资源数量及规模的权利。</p>
					<h3>十二丶保障</h3>
					<p>您同意维护，保障，并维护本站及其关联公司，免受任何及所有债权债务，包括合理的律师和专家费用，涉及到：</p>
					<ol>
						<li>违反您在本协议项下约定而产生的;</li>
						<li>您使用服务而产生的;</li>
						<li>所有的行为，并由您的用户名和密码发生的活动而产生的;</li>
						<li>任何物品或服务的出售、与您的内容或您的信息和数据的广告;</li>
						<li>包含您的内容或您的信息和数据中的任何诽谤，中伤或非法的材料;</li>
						<li>任何索赔或论点，即您的内容或您的信息和数据侵犯任何第三方的专利，版权或其他知识产权或违反隐私或公开任何第三方的权利;</li>
						<li>任何第三方的访问或使用您的内容或您的信息和数据;</li>
						<li>违反任何适用的可接受使用政策;</li>
					</ol>
					<p>您保证并声明：</p>
					<ol>
						<li>您的内容与标题符合您所在国家的法律规定；</li>
						<li>在您的内容中描述所有客户在18岁以上；</li>
						<li>您的内容不包含构成儿童色情，淫秽，兽交，暴力的真实描述，或在中国不合法的任何图像。</li>
					</ol>
					<h3>十三丶 无额外保障</h3>
					<p>您明确同意，您对服务的使用是您自行承担的风险。本站明确拒绝任何形式明示或默示的保修，包括所有的担保，包括但不限于适销性的隐含担保特定用途，所有权和非侵权。本站不保证该项服务会满足您的需求，或者说，这些服务将不中断，安全或无错误。有关宣传材料及发表任何言论都应被视为广告引用，并且不保证。您理解并同意，任何使用您的任何服务和/或数据下载或通过服务所获得的使用由您自行决定，风险自负，而您将是单方承担损害您的计算机系统或损失数据的结果和风险。</p>
					<p>本站可能会提供不属于服务（“第三方服务或软件”）的一部分，第三方产品，服务和/或软件提供给您。本站无法控制第三方服务或软件的内容。任何第三方服务或软件的使用，您将个人独自承担风险并受您与第三方达成的单独协议的条款和条件的限制。</p>
					<p>本站不提供有关任何产品和服务的保修。</p>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-block bg-red-500 text-white mb-25" data-dismiss="modal">{{trans('auth.close')}}</button>
				</div>
			</div>
		</div>
	</div>
	<div class="modal fade modal-info text-left" id="aup" aria-hidden="true" aria-labelledby="aup" role="dialog" tabindex="-1">
		<div class="modal-dialog modal-simple modal-sidebar modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close mr-15" data-dismiss="modal" aria-label="Close" style="position:absolute;">
						<span aria-hidden="true">×</span>
					</button>
					<h4 class="modal-title">{{\App\Components\Helpers::systemConfig()['website_name']}} - {{trans('auth.aup')}} <small>2019年11月28日10:49</small></h4>
				</div>
				<div class="modal-body">
					<p class="mt-15">以下是本站的可接受使用策略（AUP）。在使用本站的服务之前，您必须同意此协议，您也同意要求您的最终用户遵守此协议。本站保留随时修改本协议的权力。您同意您将定期访问本页面查看最新协议，您继续使用本站服务代表您继续同意本协议。</p>
					<p>根据您的服务发布协议，本站可自行决定终止您对服务的访问，如果您的行为违反了（或疑似违反了）本协议，或者您的最终用户违反了（或疑似违反了）本协议。</p>
					<h3>禁止非法用途和违禁用途</h3>
					作为使用服务的条款之一，您不能使用本服务用于任何非法用途，否则您将不能使用本服务。您不得使用任何有可能损害、禁用、过载或以其他方式损害本站的服务。您不得以任何方式尝试获取服务并未提供的任何信息。
					<h3>服务使用</h3>
					该服务旨在使您和您的最终用户通过互联网与他人沟通，您同意使用的服务（并要求用户使用到的服务），才发布、发送和接受信息。在适用时，您同意正确使用服务（而不是作为一个限制），举例来说，您和您的最终用户不会做出以下行为：
					<ol>
						<li>使用服务连接非法竞赛、赌博、非法彩票、发送垃圾邮件或任何重复并未经请求的信息。</li>
						<li>诽谤，辱骂，骚扰，跟踪，威胁或以其他方式侵犯他人合法权利（如隐私权和公开权）。</li>
						<li>出版，发布，上传，分发，运输或传播任何诽谤，猥亵或其他非法内容，如儿童色情或虚拟儿童色情制品。</li>
						<li>发表，张贴，上传，分发或散布煽动歧视，仇恨或暴力对待一个人或因其属于种族，宗教或民族中的任何的主题，名称，材料或信息。</li>
						<li>上传，或者以可用文件的方式以其他方式包含图像，照片，软件或其他材料受知识产权法保护（包括而不限于），而未进行限制，违反版权或商标法（或隐私或公开权），除非你拥有版权或已得到所有必要的许可这样做。</li>
						<li>使用侵犯任何版权，商标，专利，商业秘密或任何一方的其他所有权的任何材料或信息，包括图片或照片的方式通过提供服务。</li>
						<li>上传含有病毒，木马，蠕虫，定时恶意程序，以删除，损坏文件为目的，或任何其他类似的软件或程序，可能损害他人财产的文件。</li>
						<li>下载、发布任何你知道或者理应知道不能被合法传播的文件。</li>
						<li>伪造或删除任何作者归属，法律或其他适当声明，专有名称或包含在上传文件的软件或其他资料的原产地或来源标签。</li>
						<li>超出限制以至影响其他用户享受服务的行为。</li>
						<li>违反条款或可能适用于任何特定服务的其他准则中的任何内容。</li>
						<li>收集或以其他方式收集他人，包括电子邮件地址的信息，除非这是运行你的网站必须的且你的网站的隐私政策允许的（如果有的话）。</li>
						<li>违反任何适用的法律或法规。</li>
						<li>创建误导他人为目的的假身份。</li>
						<li>创建、运营一个TOR节点。</li>
						<li>扫描、嗅探其他网络主机的端口，除非这是受到对方书面允许的，其他任何理由均为不允许的。</li>
					</ol>
					<p>本站保留在任何时候透露任何信息，当这些信息被法律、法规和政府要求时，或是编辑、删除、拒绝发布任何信息或资料。全部或部分这样做由本站自行决定。</p>
					<p>本站不控制或认可服务中发现的任何内容、消息或信息。因此，本站明确拒绝承担任何责任，并不服务您参与产生的任何行为和问题。</p>
					<p>当您的服务消耗了过多资源时，如大流量下载、采矿、PT/BT，本站有权随时限制提供给您的资源，以保证该节点其他用户的正常使用。</p>
					<h3>终止/限制访问</h3>
					<p>本站没有义务监督服务，然而，本站保留审查发布于本站的任何服务和材料，并有自行决定是否删除服务和内容的权力。本站保留权利自行决定在何时终止其所有或部分服务和相关服务，或限制其访问权限，并没有义务通知您，对于违反本协议的用于，一旦服务终止，本站没有任何义务进行后续维护。</p>
					<h3>禁止SPAM</h3>
					<p>本站将会立即终止任何被认为发送垃圾邮件或者大量未经请求的邮件的服务，并且没有任何义务进行恢复。</p>
					<h3>注意涉及侵权</h3>
					<p>本站尊重知识产权并要求我们的客户做到相同的责任，如果您认为您的知识产权被侵犯，或者我们认为您的内容侵犯了他人的知识产权，请向本站提供以下资料：
						获得代表版权授权或其他知识产权利益所有者的个人的电子签名或亲笔签名；</p>
					<ol>
						<li>版权作品，或者你声称受到侵犯其他知识产权的描述；</li>
						<li>描述您声称侵权服务的材料；</li>
						<li>您的地址，电话号码和电子邮件地址；</li>
						<li>通过你的陈述，您有充分理由相信该有争议内容未经著作所有人或者代理人授权或法律授权的内容；</li>
						<li>一份声明，能够证明您上述信息是正确的，您是版权或者知识产权、著作的所有者、代理人、法定代表或者被授权者的信息。</li>
					</ol>
					您可以发送侵权通知到：<br>
					<a href="mailto:{{\App\Components\Helpers::systemConfig()['webmaster_email']}}">{{\App\Components\Helpers::systemConfig()['webmaster_email']}}</a>
					请不要通过代理发送信息或者发送其他任何信息。
					<h3>通知和移除程序</h3>
					<p>本站保留在任何时候删除被认为侵权或者收到侵权声明的服务和内容。本站保留随时有权禁止访问或者删除内容的方式阻止被侵权内容访问或任何声称是基于事实的侵权内容。</p>

				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-block bg-red-500 text-white mb-25" data-dismiss="modal">{{trans('auth.close')}}</button>
				</div>
			</div>
		</div>
	</div>
	@endsection
@section('script')
	<!--[if lt IE 11]>
	<script src="/assets/custom/Plugin/sweetalert2/polyfill.min.js" type="text/javascript"></script>
	<![endif]-->
	<script src="/assets/custom/Plugin/sweetalert2/sweetalert2.min.js" type="text/javascript"></script>
	<script src="/assets/global/vendor/bootstrap-select/bootstrap-select.min.js" type="text/javascript"></script>
	<script src="/assets/global/js/Plugin/bootstrap-select.js" type="text/javascript"></script>
	<script type="text/javascript">
		@if(!\App\Components\Helpers::systemConfig()['sensitiveType'])
        function getUsername() {
            let username = $("#emailHead").val();
            const emailTail = $("#emailTail").val();
            if (username.trim() === '') {
                swal.fire({title: '{{trans('auth.username_null')}}', type: 'warning', timer: 1500});
                return false;
            }
            username = username.trim() + '@' + emailTail;
            $("#username").val(username);
        }

        $('#register-form').submit(function () {
            getUsername();
            // 先检查Google reCAPTCHA有没有进行验证
            if ($('#g-recaptcha-response').val() === '') {
                Msg(false, "{{trans('login.required_captcha')}}", 'error');
                return false;
            }
            return true; // return false to cancel form action
        });

		@endif

        // 发送注册验证码
        function sendVerifyCode() {
            let flag = true; // 请求成功与否标记
			@if(!\App\Components\Helpers::systemConfig()['sensitiveType'])
            getUsername();
					@endif
            const username = $("#username").val();
            if (username.trim() === '') {
                swal.fire({title: '{{trans('auth.username_null')}}', type: 'warning', timer: 1500});
                return false;
            }

            $.ajax({
                type: "POST",
                url: "/sendCode",
                async: false,
                data: {_token: '{{csrf_token()}}', username: username},
                dataType: 'json',
                success: function (ret) {
                    if (ret.status === 'fail') {
                        swal.fire({title: ret.message, type: 'error', timer: 1000, showConfirmButton: false});
                        $("#sendCode").attr('disabled', false);
                        flag = false;
                    } else {
                        swal.fire({title: '{{trans('auth.captcha_send')}}', type: 'error'});
                        $("#sendCode").attr('disabled', true);
                        flag = true;
                    }
                },
                error: function () {
                    swal.fire({title: '{{trans('auth.captcha_send')}}', type: 'error'});
                    flag = false;
                }
            });

            // 请求成功才开始倒计时
            if (flag) {
                // 60秒后重新发送
                let left_time = 60;
                const tt = window.setInterval(function () {
                    left_time = left_time - 1;
                    if (left_time <= 0) {
                        window.clearInterval(tt);
                        $("#sendCode").attr('disabled', false).val('{{trans('auth.send')}}');
                    } else {
                        $("#sendCode").val(left_time);
                    }
                }, 1000);
            }
        }

        $('#register-form').submit(function (event) {
            // 先检查Google reCAPTCHA有没有进行验证
            if ($('#g-recaptcha-response').val() === '') {
                Msg(false, "{{trans('login.required_captcha')}}", 'error');
                return false;
            }
        });

        // 生成提示
        function Msg(clear, msg, type) {
            if (!clear) $('.register-form .alert').remove();

            var typeClass = 'alert-danger',
                clear = clear ? clear : false,
                $elem = $('.register-form');
            type === 'error' ? typeClass = 'alert-danger' : typeClass = 'alert-success';

            const tpl = '<div class="alert ' + typeClass + '">' +
                '<button type="button" class="close" onclick="$(this).parent().remove();"></button>' +
                '<span> ' + msg + ' </span></div>';

            if (!clear) {
                $elem.prepend(tpl);
            } else {
                $('.register-form .alert').remove();
            }
        }
	</script>
@endsection
