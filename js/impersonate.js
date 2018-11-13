/* global OC, $ */
(function(OC, $){

	$(document).ready(function() {
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

		function impersonateDialog(event) {
			let userId = event.target.closest('.row').dataset.id;
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
		}

		let registerFunction = function (delay) {
			if(OCA.Settings === undefined) {
				delay = delay * 2;
				if(delay === 0) {
					delay = 15;
				}
				if(delay > 500) {
					console.warn("Could not register impersonate script");
					return;
				}
				setTimeout(function() {registerFunction(delay)}, delay);
			} else {
				OCA.Settings.UserList.registerAction('icon-user', t('impersonate', 'Impersonate'), impersonateDialog)
			}
		};
		registerFunction(0);
	});

})(OC, $);
