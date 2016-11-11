$(function() {
	$('.flash').first().delay(5000).hide('slow', function hideNext() {
		$(this).next('.flash').hide('slow', hideNext);
	});
});
