$(document).ready(function () {

	$("#logout").attr("href","#");

	var text = t(
		'core',
		'<a href="{docUrl}">{displayText}</a>',
		{
			docUrl: OC.generateUrl('apps/files'),
			displayText: "Logged in as " + OC.getCurrentUser().uid
		}
	);

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
		).promise();

		promisObj.done(function () {
			OC.redirect(OC.generateUrl('settings/users'))
		});
	}

	$('#settings ul li:last').on('click', function (event) {
		event.preventDefault();
		var userId = $("#expandDisplayName").text();
		logoutHandler(userId);
	});
});
