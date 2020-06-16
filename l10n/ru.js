OC.L10N.register(
    "impersonate",
    {
    "Could not impersonate user" : "Не удалось войти в систему под именем другого пользователя",
    "Are you sure you want to impersonate \"{userId}\"?" : "Войти под именем «{userId}»?",
    "Impersonate user" : "Вход под другим именем",
    "Impersonate" : "Войти под именем другого пользователя",
    "Logged in as {uid}" : "Выполнен вход под именем «{uid}»",
    "User not found" : "Пользователь не найден",
    "Insufficient permissions to impersonate user" : "Недостаточно прав доступа для входа в систему под именем другого пользователя",
    "Cannot impersonate the user because it was never logged in." : "Невозможно войти в систему под именем другого пользователя , поскольку он никогда не в в систему.",
    "Cannot impersonate yourself." : "Невозможно входить под именем другого пользователя в свою учётную запись.",
    "Impersonate other users" : "Вход под именем других пользователей",
    "By installing the impersonate app of your Nextcloud you enable administrators to impersonate other users on the Nextcloud server. This is especially useful for debugging issues reported by users.\n\nTo impersonate a user an administrator has to simply follow the following four steps:\n\n1. Login as administrator to Nextcloud\n2. Open the user administration interface\n3. Select the impersonate button on the affected user\n4. Confirm the impersonation\n\nThe administrator is then logged-in as the user, to switch back to the regular user account they simply have to press the logout button.\n\n**Note:**\n\n- This app is not compatible with instances that have encryption enabled.\n- While impersonate actions are logged note that actions performed impersonated will be logged as the impersonated user.\n- Impersonating an user is only possible after their first login." : "Установив приложение для входа под другими пользователями Nextcloud, вы разрешаете администраторам выдавать себя за других пользователей на сервере Nextcloud. Это особенно полезно для отладки проблем, о которых сообщают пользователи.\n\nЧтобы войти под другим пользователем, администратор должен выполнить следующие шаги:\n\n1. Войти как администратор в Nextcloud\n2. Открыть интерфейс управления пользователями\n3. Использовать кнопку входа под именем выбранного пользователя.\n4. Подтвердить вход.\n\nПосле этого администратор входит в систему как пользователь, а чтобы вернуться к обычной учетной записи пользователя, просто нажмите кнопку выхода.\n\n**Примечание:**\n\n- Это приложение несовместимо с конфигурациями, в которых включено шифрование.\n- Во время входа под другим пользователем выполняемые действия заносятся в журнал как действия, выполненные самим пользователем.\n- Вход под другим пользователем возможен только после его первого входа в систему.",
    "These groups will be able to impersonate users they are allowed to administrate. If you remove all groups, every group administrator will be allowed to impersonate." : "Администраторы этих групп могут входить под именами пользователей своих групп. При пустом списке эта функция доступна всем администраторам групп."
},
"nplurals=4; plural=(n%10==1 && n%100!=11 ? 0 : n%10>=2 && n%10<=4 && (n%100<12 || n%100>14) ? 1 : n%10==0 || (n%10>=5 && n%10<=9) || (n%100>=11 && n%100<=14)? 2 : 3);");
