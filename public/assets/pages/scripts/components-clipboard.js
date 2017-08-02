// ClipboardJS

var ComponentsClipboard = function() {

    return {
        //main function to initiate the module
        init: function() {
        	var paste_text;

        	$('.mt-clipboard').each(function(){
        		var clipboard = new Clipboard(this);	

        		clipboard.on('success', function(e) {
				    paste_text = e.text;
				    console.log(paste_text);
				});
        	});

        	$('.mt-clipboard').click(function(){
    			if($(this).data('clipboard-paste') == true){
    				if(paste_text){
        				var paste_target = $(this).data('paste-target');
        				$(paste_target).val(paste_text);
        				$(paste_target).html(paste_text);
        			} else {
        				alert('No text was copied or cut.');
        			}
        		} 
    		});
        }
    }

}();

jQuery(document).ready(function() {
    ComponentsClipboard.init();
});