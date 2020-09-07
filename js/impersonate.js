/* global OC */
(function(OC) {
	function impersonate(userId) {
		var xhr = new XMLHttpRequest()
		xhr.onreadystatechange = function(data) {
			if (xhr.readyState === XMLHttpRequest.DONE) {
				if (xhr.status === 0 || (xhr.status >= 200 && xhr.status < 300)) {
					window.location = OC.generateUrl('/')
				} else {
					OC.dialogs.alert(JSON.parse(xhr.response).message, t('impersonate', 'Could not impersonate user'), undefined, undefined)
				}
			}
		}
		xhr.open('POST', OC.generateUrl('apps/impersonate/user'))
		xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')
		xhr.send('userId=' + encodeURIComponent(userId) + '&requesttoken=' + encodeURIComponent(OC.requestToken))
	}

	function impersonateDialog(event) {
		var userId = event.target.closest('.row').dataset.id
		OC.dialogs.confirm(
			t('impersonate', 'Are you sure you want to impersonate "{userId}"?', { userId: userId }),
			t('impersonate', 'Impersonate user'),
			function(result) {
				if (result) {
					impersonate(userId)
				}
			},
			true
		)
	}

	var registerFunction = function(delay) {
		delay = delay || 0
		if (OCA.Settings === undefined) {
			delay = delay * 2
			if (delay === 0) {
				delay = 15
			}
			if (delay > 500) {
				console.error('Could not register impersonate script')
				return
			}
			setTimeout(function() { registerFunction(delay) }, delay)
		} else {
			OCA.Settings.UserList.registerAction('icon-user', t('impersonate', 'Impersonate'), impersonateDialog)
		}
	}

	document.addEventListener('DOMContentLoaded', registerFunction)

})(OC)
