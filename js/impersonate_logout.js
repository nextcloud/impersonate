document.addEventListener('DOMContentLoaded', function() {

	$("#logout").attr("href","#");

	var text = '<a href="' + OC.generateUrl('apps/files') + '">' +
		t('impersonate', 'Logged in as {uid}', {uid: OC.getCurrentUser().uid}) +
		'</a>';

	OC.Notification.showHtml(
		text,
		{
			isHTML: true,
			timeout: 15
		}
	);

	function logoutHandler(userId) {
		var promisObj = $.post(
			OC.generateUrl('apps/impersonate/logout'),
			{userId: userId}
		).promise()

		promisObj.done(function () {
			OC.redirect(OC.generateUrl('settings/users'))
		});
	}

	$('#settings ul li:last').on('click', function (event) {
		event.preventDefault()
		var userId = $("#expandDisplayName").text()
		logoutHandler(userId)
	})
})
