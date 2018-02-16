/* global OC, $ */
(function(OC, $){

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

		var $impersonateAction = $('<li>').append(
			$('<a>').attr('href', '#').addClass('menuitem action-impersonate permanent')
				.append($('<span>').addClass('icon icon-user'))
				.append($('<span>').text(t('impersonate', 'Impersonate')))
		);

		$impersonateAction.insertAfter($(".userActionsMenu").find("li:last-child"));

		$('body').on('click', '.action-impersonate', function() {
			var userId = $(this).parents('tr').find('.name').text();
			OC.dialogs.confirm(
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

})(OC, $);
