/*!
 * FormValidation (http://formvalidation.io)
 * The best jQuery plugin to validate form fields. Support Bootstrap, Foundation, Pure, SemanticUI, UIKit and custom frameworks
 *
 * @version     v0.8.1, built on 2016-07-29 1:10:56 AM
 * @author      https://twitter.com/formvalidation
 * @copyright   (c) 2013 - 2016 Nguyen Huu Phuoc
 * @license     http://formvalidation.io/license/
 */
!function(a){FormValidation.Framework.Semantic=function(b,c){c=a.extend(!0,{button:{selector:'[type="submit"]:not([formnovalidate])',disabled:"disabled"},control:{valid:"",invalid:""},err:{clazz:"ui red pointing label",parent:"^.*(field|column).*$"},icon:{valid:null,invalid:null,validating:null,feedback:"fv-control-feedback"},row:{selector:".fields",valid:"fv-has-success",invalid:"error",feedback:"fv-has-feedback"}},c),FormValidation.Base.apply(this,[b,c])},FormValidation.Framework.Semantic.prototype=a.extend({},FormValidation.Base.prototype,{_fixIcon:function(a,b){var c=a.attr("type");if("checkbox"===c||"radio"===c){var d=a.parent();d.hasClass(c)&&b.insertAfter(d)}},_createTooltip:function(a,b,c){var d=a.data("fv.icon");if(d)switch(d.popup("exists")&&d.popup("remove popup").popup("destroy"),c){case"popover":d.css({cursor:"pointer"}).popup({content:b,position:"top center"});break;case"tooltip":default:d.css({cursor:"pointer"}).popup({content:b,position:"top center",variation:"inverted"})}},_destroyTooltip:function(a,b){var c=a.data("fv.icon");c&&c.popup("exists")&&c.css({cursor:""}).popup("remove popup").popup("destroy")},_hideTooltip:function(a,b){var c=a.data("fv.icon");c&&c.popup("hide")},_showTooltip:function(a,b){var c=a.data("fv.icon");c&&c.popup("show")}})}(jQuery);