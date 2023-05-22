(function(OC) {
	function logoutHandler() {
		var xhr = new XMLHttpRequest()
		xhr.onreadystatechange = function(data) {
			if (xhr.readyState === XMLHttpRequest.DONE) {
				if (xhr.status === 0 || (xhr.status >= 200 && xhr.status < 400)) {
					OC.redirect(OC.generateUrl('settings/users'))
				} else {
					OC.dialogs.alert(JSON.parse(xhr.response).message, t('impersonate', 'Could not log out, please try again'), undefined, undefined)
				}
			}
		}
		xhr.open('POST', OC.generateUrl('apps/impersonate/logout'))
		xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')
		xhr.send('requesttoken=' + encodeURIComponent(OC.requestToken))
	}
	function modifyLogout () {
		document.getElementById('logout').onclick = (event) => {
			event.preventDefault()
			logoutHandler()
		}
		document.getElementById('logout').getElementsByTagName('a')[0].setAttribute('href', '#')

		var text = '<a href="' + OC.generateUrl('apps/files') + '">' +
			t('impersonate', 'Logged in as {name} ({uid})', {uid: OC.getCurrentUser().uid, name: OC.getCurrentUser().displayName}) +
			'</a>';

		OC.Notification.showHtml(
			text,
			{
				isHTML: true,
				timeout: 0
			}
		);
	}

	var enableImpersonateLogout = function (event, delay) {
		delay = delay || 0
		if (document.getElementById('logout') === null) {
			delay = delay * 2
			if (delay === 0) {
				delay = 15
			}
			if (delay > 500) {
				console.error('Could not register impersonate script')
				return
			}
			setTimeout(function () {
				enableImpersonateLogout(event, delay)
			}, delay)
		} else {
			modifyLogout()
		}
	}

	document.addEventListener('DOMContentLoaded', enableImpersonateLogout)
})(OC)
