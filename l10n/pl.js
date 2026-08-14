OC.L10N.register(
    "sharecleanup",
    {
    "Share Cleanup": "Czyszczenie udostępnień",
    "Automatically ends shares older than the configured number of days. Shared files are tagged with the end date of the share, and the sharing user is notified at 90 % of the lifetime. Shares with their own expiration date are never ended. The files themselves are never deleted.": "Automatycznie kończy udostępnienia starsze niż skonfigurowana liczba dni. Udostępniane pliki są oznaczane datą wygaśnięcia, a użytkownik udostępniający otrzymuje powiadomienie po upływie 90% czasu życia. Udostępnienia z własną datą wygaśnięcia nigdy nie są kończone. Same pliki nie są usuwane.",
    "Maximum share age (days)": "Maksymalny wiek udostępnienia (dni)",
    "Users are notified after {notify} days (90 %), the share ends after {total} days. Shares with their own expiration date are never ended.": "Użytkownicy są powiadamiani po {notify} dniach (90%), udostępnienie kończy się po {total} dniach. Udostępnienia z własną datą wygaśnięcia nie są kończone.",
    "Dry-run mode (only log, end nothing)": "Tryb testowy (tylko loguj, nic nie kończ)",
    "Enabled by default. Test with \"occ sharecleanup:run --dry-run\" and check the log, then disable.": "Włączone domyślnie. Przetestuj za pomocą \"occ sharecleanup:run --dry-run\" i sprawdź logi, a następnie wyłącz.",
    "Save": "Zapisz",
    "Settings saved.": "Ustawienia zapisane.",
    "Error saving settings: ": "Błąd podczas zapisywania ustawień: ",
    "Please enter a number of days (minimum 1).": "Wprowadź liczbę dni (minimum 1).",
    "Manual run: occ sharecleanup:run [--days=N] [--dry-run] [--force]": "Uruchomienie ręczne: occ sharecleanup:run [--days=N] [--dry-run] [--force]",
    "Share of \"%s\" ends soon": "Udostępnienie \"%s\" wkrótce się zakończy",
    "Your share of \"%1$s\" ends automatically on %2$s. The file itself is not deleted. If the share is still needed, share the file again afterwards.": "Twoje udostępnienie \"%1$s\" zakończy się automatycznie %2$s. Sam plik nie zostanie usunięty. Jeśli udostępnienie jest nadal potrzebne, udostępnij plik ponownie."
},
    "nplurals=2; plural=(n != 1);"
);
