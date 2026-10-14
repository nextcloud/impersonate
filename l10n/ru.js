OC.L10N.register(
    "impersonate",
    {
    "An administrator ({actor}) accessed your account" : "Администратор ({actor}) вошёл в вашу учётную запись",
    "User not found" : "Пользователь не найден",
    "Insufficient permissions to impersonate user" : "Недостаточно прав для входа от имени другого пользователя",
    "Cannot impersonate the user because it was never logged in" : "Невозможно войти от имени пользователя: он ещё ни разу не входил в систему",
    "Cannot impersonate yourself" : "Нельзя войти от имени самого себя",
    "Impersonation cannot proceed if any application specified in the impersonation notifications setting (e.g., 'impersonate.notifications') is not active." : "Вход от имени пользователя невозможен, если не активно какое-либо приложение, указанное в параметре уведомлений о входе от имени (например, 'impersonate.notifications').",
    "Impersonate" : "Войти от имени",
    "An administrator ('%1$s') accessed your account on %2$s at %3$s." : "Администратор («%1$s») вошёл в вашу учётную запись: %2$s, %3$s.",
    "An administrator accessed your account" : "Администратор вошёл в вашу учётную запись",
    "This action may be necessary as part of platform maintenance. Please contact this administrator if you find this action suspicious or if you would like more information about it." : "Это может потребоваться при обслуживании платформы. Если это действие кажется вам подозрительным или вы хотите узнать подробности, свяжитесь с этим администратором.",
    "Impersonate other users" : "Вход от имени других пользователей",
    "By installing the impersonate app of your Nextcloud you enable administrators to impersonate other users on the Nextcloud server. This is especially useful for debugging issues reported by users.\n\nTo impersonate a user an administrator has to simply follow the following four steps:\n\n1. Login as administrator to Nextcloud.\n2. Open users administration interface.\n3. Select the impersonate button on the affected user.\n4. Confirm the impersonation.\n\nThe administrator is then logged-in as the user, to switch back to the regular user account they simply have to press the logout button.\n\n**Note:**\n\n- This app is not compatible with instances that have encryption enabled.\n- While impersonate actions are logged note that actions performed impersonated will be logged as the impersonated user.\n- Impersonating a user is only possible after their first login.\n- You can limit which users/groups can use impersonation in Administration settings > Additional settings." : "Это приложение позволяет администраторам Nextcloud входить от имени других пользователей. Это особенно полезно для отладки проблем, о которых сообщают пользователи.\n\nЧтобы войти от имени пользователя, администратору нужно:\n\n1. Войти в Nextcloud как администратор.\n2. Открыть управление пользователями.\n3. Нажать кнопку входа от имени для нужного пользователя.\n4. Подтвердить вход.\n\nПосле этого администратор войдёт в систему как этот пользователь. Чтобы вернуться в свою учётную запись, достаточно нажать кнопку выхода.\n\n**Примечание:**\n\n- Приложение несовместимо с серверами, на которых включено шифрование.\n- Действия при входе от имени пользователя журналируются, но записываются на имя этого пользователя.\n- Войти от имени пользователя можно только после его первого входа в систему.\n- Круг пользователей и групп, которым разрешён вход от имени других, можно ограничить в «Параметры администрирования» > «Дополнительные параметры».",
    "Authorized groups" : "Разрешённые группы",
    "These groups will be able to impersonate users they are allowed to administrate. If you remove all groups, every group administrator will be allowed to impersonate." : "Администраторы этих групп могут входить от имени пользователей своих групп. При пустом списке эта функция доступна всем администраторам групп.",
    "Could not impersonate user" : "Не удалось войти от имени пользователя",
    "Impersonate user" : "Войти от имени пользователя",
    "Are you sure you want to impersonate \"{userId}\"?" : "Войти от имени «{userId}»?",
    "Could not log out, please try again" : "Не удалось выйти из системы, повторите попытку",
    "Logged in as {name} ({uid})" : "Вход выполнен как {name} ({uid})"
},
"nplurals=4; plural=(n%10==1 && n%100!=11 ? 0 : n%10>=2 && n%10<=4 && (n%100<12 || n%100>14) ? 1 : n%10==0 || (n%10>=5 && n%10<=9) || (n%100>=11 && n%100<=14)? 2 : 3);");
