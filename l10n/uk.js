OC.L10N.register(
    "sharecleanup",
    {
    "Share Cleanup": "Очищення спільного доступу",
    "Automatically ends shares older than the configured number of days. Shared files are tagged with the end date of the share, and the sharing user is notified at 90 % of the lifetime. Shares with their own expiration date are never ended. The files themselves are never deleted.": "Автоматично завершує спільний доступ, старіший за налаштовану кількість днів. Спільні файли позначаються датою закінчення, а користувач отримує сповіщення на 90% терміну дії. Ресурси з власним терміном дії ніколи не завершуються. Самі файли не видаляються.",
    "Maximum share age (days)": "Максимальний вік спільного доступу (днів)",
    "Users are notified after {notify} days (90 %), the share ends after {total} days. Shares with their own expiration date are never ended.": "Користувачі отримують сповіщення через {notify} днів (90%), спільний доступ закінчується через {total} днів. Ресурси з власним терміном дії не закриваються.",
    "Dry-run mode (only log, end nothing)": "Тестовий режим (лише логування, нічого не завершувати)",
    "Enabled by default. Test with \"occ sharecleanup:run --dry-run\" and check the log, then disable.": "Увімкнено за замовчуванням. Перевірте за допомогою \"occ sharecleanup:run --dry-run\", перегляньте лог, потім вимкніть.",
    "Save": "Зберегти",
    "Settings saved.": "Налаштування збережено.",
    "Error saving settings: ": "Помилка збереження налаштувань: ",
    "Please enter a number of days (minimum 1).": "Введіть кількість днів (мінімум 1).",
    "Manual run: occ sharecleanup:run [--days=N] [--dry-run] [--force]": "Запуск вручну: occ sharecleanup:run [--days=N] [--dry-run] [--force]",
    "Share of \"%s\" ends soon": "Спільний доступ до \"%s\" скоро завершиться",
    "Your share of \"%1$s\" ends automatically on %2$s. The file itself is not deleted. If the share is still needed, share the file again afterwards.": "Ваш спільний доступ до \"%1$s\" автоматично завершиться %2$s. Сам файл не видаляється. Якщо спільний доступ все ще потрібен, поділіться файлом знову."
},
    "nplurals=2; plural=(n != 1);"
);
