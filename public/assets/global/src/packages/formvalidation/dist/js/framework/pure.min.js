/*!
 * FormValidation (http://formvalidation.io)
 * The best jQuery plugin to validate form fields. Support Bootstrap, Foundation, Pure, SemanticUI, UIKit and custom frameworks
 *
 * @version     v0.8.1, built on 2016-07-29 1:10:56 AM
 * @author      https://twitter.com/formvalidation
 * @copyright   (c) 2013 - 2016 Nguyen Huu Phuoc
 * @license     http://formvalidation.io/license/
 */
!function(a){FormValidation.Framework.Pure=function(b,c){c=a.extend(!0,{button:{selector:'[type="submit"]:not([formnovalidate])',disabled:"pure-button-disabled"},err:{clazz:"fv-help-block",parent:"^.*pure-control-group.*$"},icon:{valid:null,invalid:null,validating:null,feedback:"fv-control-feedback"},row:{selector:".pure-control-group",valid:"fv-has-success",invalid:"fv-has-error",feedback:"fv-has-feedback"}},c),FormValidation.Base.apply(this,[b,c])},FormValidation.Framework.Pure.prototype=a.extend({},FormValidation.Base.prototype,{_fixIcon:function(a,b){var c=this._namespace,d=a.attr("type"),e=(a.attr("data-"+c+"-field"),a.parent());("checkbox"===d||"radio"===d)&&e.is("label")&&b.insertAfter(e)}})}(jQuery);