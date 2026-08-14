OC.L10N.register(
    "sharecleanup",
    {
    "Share Cleanup": "Очистка общих ресурсов",
    "Automatically ends shares older than the configured number of days. Shared files are tagged with the end date of the share, and the sharing user is notified at 90 % of the lifetime. Shares with their own expiration date are never ended. The files themselves are never deleted.": "Автоматически завершает общий доступ, срок действия которого превышает заданное количество дней. Общие файлы помечаются датой окончания, а пользователь получает уведомление по истечении 90% срока. Общие ресурсы с собственным сроком действия никогда не завершаются. Сами файлы не удаляются.",
    "Maximum share age (days)": "Максимальный возраст общего доступа (дней)",
    "Users are notified after {notify} days (90 %), the share ends after {total} days. Shares with their own expiration date are never ended.": "Пользователи уведомляются через {notify} дней (90%), общий доступ завершается через {total} дней. Ресурсы с собственной датой истечения никогда не закрываются.",
    "Dry-run mode (only log, end nothing)": "Тестовый режим (только логирование, ничего не завершать)",
    "Enabled by default. Test with \"occ sharecleanup:run --dry-run\" and check the log, then disable.": "Включено по умолчанию. Проверьте с помощью \"occ sharecleanup:run --dry-run\", просмотрите лог, затем отключите.",
    "Save": "Сохранить",
    "Settings saved.": "Настройки сохранены.",
    "Error saving settings: ": "Ошибка сохранения настроек: ",
    "Please enter a number of days (minimum 1).": "Введите количество дней (минимум 1).",
    "Manual run: occ sharecleanup:run [--days=N] [--dry-run] [--force]": "Запуск вручную: occ sharecleanup:run [--days=N] [--dry-run] [--force]",
    "Share of \"%s\" ends soon": "Общий доступ к \"%s\" скоро завершится",
    "Your share of \"%1$s\" ends automatically on %2$s. The file itself is not deleted. If the share is still needed, share the file again afterwards.": "Ваш общий доступ к \"%1$s\" автоматически завершится %2$s. Сам файл не удаляется. Если общий доступ все еще нужен, поделитесь файлом снова."
},
    "nplurals=2; plural=(n != 1);"
);
