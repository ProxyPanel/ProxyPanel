var ComponentsBootstrapMultiselect = function () {

    return {
        //main function to initiate the module
        init: function () {
        	$('.mt-multiselect').each(function(){
        		var btn_class = $(this).attr('class');
        		var clickable_groups = ($(this).data('clickable-groups')) ? $(this).data('clickable-groups') : false ;
        		var collapse_groups = ($(this).data('collapse-groups')) ? $(this).data('collapse-groups') : false ;
        		var drop_right = ($(this).data('drop-right')) ? $(this).data('drop-right') : false ;
        		var drop_up = ($(this).data('drop-up')) ? $(this).data('drop-up') : false ;
        		var select_all = ($(this).data('select-all')) ? $(this).data('select-all') : false ;
        		var width = ($(this).data('width')) ? $(this).data('width') : '' ;
        		var height = ($(this).data('height')) ? $(this).data('height') : '' ;
        		var filter = ($(this).data('filter')) ? $(this).data('filter') : false ;

        		// advanced functions
        		var onchange_function = function(option, checked, select) {
	                alert('Changed option ' + $(option).val() + '.');
	            }
	            var dropdownshow_function = function(event) {
	                alert('Dropdown shown.');
	            }
	            var dropdownhide_function = function(event) {
	                alert('Dropdown Hidden.');
	            }

	            // init advanced functions
	            var onchange = ($(this).data('action-onchange') == true) ? onchange_function : '';
	            var dropdownshow = ($(this).data('action-dropdownshow') == true) ? dropdownshow_function : '';
	            var dropdownhide = ($(this).data('action-dropdownhide') == true) ? dropdownhide_function : '';

	            // template functions
	            // init variables
	            var li_template;
	            if ($(this).attr('multiple')){
	            	li_template = '<li class="mt-checkbox-list"><a href="javascript:void(0);"><label class="mt-checkbox"> <span></span></label></a></li>';
        		} else {
        			li_template = '<li><a href="javascript:void(0);"><label></label></a></li>';
         		}

	            // init multiselect
        		$(this).multiselect({
        			enableClickableOptGroups: clickable_groups,
        			enableCollapsibleOptGroups: collapse_groups,
        			disableIfEmpty: true,
        			enableFiltering: filter,
        			includeSelectAllOption: select_all,
        			dropRight: drop_right,
        			buttonWidth: width,
        			maxHeight: height,
        			onChange: onchange,
        			onDropdownShow: dropdownshow,
        			onDropdownHide: dropdownhide,
        			buttonClass: btn_class,
        			//optionClass: function(element) { return "mt-checkbox"; },
        			//optionLabel: function(element) { console.log(element); return $(element).html() + '<span></span>'; },
        			/*templates: {
		                li: li_template,
		            }*/
        		});   
        	});
         	
        }
    };

}();

jQuery(document).ready(function() {    
   ComponentsBootstrapMultiselect.init(); 
});