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
		var $newColumn = $("#userlist").find("tr:first-child");
		$('<th id="impersonateId" scope="col">'+t('impersonate', 'Impersonate') +'</th>').insertAfter($newColumn.find("#headerName"));
		$('<td><a class="action permanent impersonate" href="#" title="' +
			t('impersonate', 'Impersonate') + '">' +
			'<img class="svg permanent action" src="' + OC.imagePath('core','actions/user.svg') + '" />' +
			'</a></td>')
			.insertAfter('#userlist .name');

		$('#userlist').on('click', '.impersonate', function() {
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
