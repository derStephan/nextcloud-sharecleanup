OC.L10N.register(
    "sharecleanup",
    {
    "Share Cleanup": "Share Cleanup",
    "Automatically ends shares older than the configured number of days. Shared files are tagged with the end date of the share, and the sharing user is notified at 90 % of the lifetime. Shares with their own expiration date are never ended. The files themselves are never deleted.": "Beendet automatisch Freigaben, die älter als die konfigurierte Anzahl Tage sind. Geteilte Dateien werden mit dem Enddatum der Freigabe versehen, und der teilende Benutzer wird bei 90 % der Laufzeit benachrichtigt. Freigaben mit eigenem Ablaufdatum werden nie beendet. Die Dateien selbst werden niemals gelöscht.",
    "Maximum share age (days)": "Maximales Share-Alter (Tage)",
    "Users are notified after {notify} days (90 %), the share ends after {total} days. Shares with their own expiration date are never ended.": "Benutzer werden nach {notify} Tagen (90 %) benachrichtigt, die Freigabe endet nach {total} Tagen. Freigaben mit eigenem Ablaufdatum werden nie beendet.",
    "Dry-run mode (only log, end nothing)": "Testmodus (nur protokollieren, nichts beenden)",
    "Enabled by default. Test with \"occ sharecleanup:run --dry-run\" and check the log, then disable.": "Standardmäßig aktiviert. Mit \"occ sharecleanup:run --dry-run\" testen und das Protokoll prüfen, danach deaktivieren.",
    "Save": "Speichern",
    "Settings saved.": "Einstellungen gespeichert.",
    "Error saving settings: ": "Fehler beim Speichern der Einstellungen: ",
    "Please enter a number of days (minimum 1).": "Bitte eine Anzahl Tage eingeben (mindestens 1).",
    "Manual run: occ sharecleanup:run [--days=N] [--dry-run] [--force]": "Manueller Lauf: occ sharecleanup:run [--days=N] [--dry-run] [--force]",
    "Share of \"%s\" ends soon": "Freigabe von \"%s\" endet bald",
    "Your share of \"%1$s\" ends automatically on %2$s. The file itself is not deleted. If the share is still needed, share the file again afterwards.": "Deine Freigabe von \"%1$s\" endet automatisch am %2$s. Die Datei selbst wird nicht gelöscht. Wird die Freigabe noch benötigt, teile die Datei danach erneut."
},
    "nplurals=2; plural=(n != 1);"
);
