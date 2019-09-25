$j = jQuery.noConflict();

$j(function(){
	$j(document).on('click', '#add_new_line button', function(){
			var line = $j('.first_line').clone().removeClass('first_line').find('input').val('');
			$j('#add_new_line').before(line);
	});
});

