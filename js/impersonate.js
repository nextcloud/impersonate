(function(){

	$(document).ready(function() {
		if(!OC.isUserAdmin()) {
			return;
		}

		function impersonate(userId) {
			$.post(
				OC.generateUrl('apps/impersonate/user'),
				{ userId: userId }
			).done(function() {
				window.location = OC.generateUrl('apps/files');
			}).fail(function( result ) {
				OC.dialogs.alert(result.responseJSON.message, t('impersonate', 'Could not impersonate user'));
			});
		}

        $('<li><a href="#" class="menuitem permanent impersonate">' +
            '<span class="icon icon-user"></span><span>Impersonate</span></a></li>').insertAfter(
                $(".userActionsMenu").find("li:last-child"));

		$('body').on('click', '.impersonate', function() {
			var userId = $(this).parents('tr').find('.name').text();
			OCdialogs.confirm(
				t('impersonate', 'Are you sure you want to impersonate "{userId}"?', {userId: userId}),
				t('impersonate', 'Impersonate user' ),
				function(result) {
					if (result) {
						impersonate(userId);
					}
				},
				true
			);
		});

	});

})();
