OC.L10N.register(
    "sharecleanup",
    {
    "Share Cleanup": "Pulizia condivisioni",
    "Automatically ends shares older than the configured number of days. Shared files are tagged with the end date of the share, and the sharing user is notified at 90 % of the lifetime. Shares with their own expiration date are never ended. The files themselves are never deleted.": "Termina automaticamente le condivisioni più vecchie rispetto al numero di giorni configurato. I file condivisi vengono etichettati con la data di fine e l'utente che ha condiviso viene avvisato al 90% del ciclo di vita. Le condivisioni con una propria data di scadenza non vengono mai terminate. I file non vengono eliminati.",
    "Maximum share age (days)": "Età massima della condivisione (giorni)",
    "Users are notified after {notify} days (90 %), the share ends after {total} days. Shares with their own expiration date are never ended.": "Gli utenti vengono avvisati dopo {notify} giorni (90%), la condivisione termina dopo {total} giorni. Le condivisioni con una propria data di scadenza non vengono mai terminate.",
    "Dry-run mode (only log, end nothing)": "Modalità di prova (solo log, non terminare nulla)",
    "Enabled by default. Test with \"occ sharecleanup:run --dry-run\" and check the log, then disable.": "Abilitato per impostazione predefinita. Testare con \"occ sharecleanup:run --dry-run\" e controllare il log, quindi disabilitare.",
    "Save": "Salva",
    "Settings saved.": "Impostazioni salvate.",
    "Error saving settings: ": "Errore durante il salvataggio delle impostazioni: ",
    "Please enter a number of days (minimum 1).": "Inserire un numero di giorni (minimo 1).",
    "Manual run: occ sharecleanup:run [--days=N] [--dry-run] [--force]": "Esecuzione manuale: occ sharecleanup:run [--days=N] [--dry-run] [--force]",
    "Share of \"%s\" ends soon": "La condivisione di \"%s\" terminerà a breve",
    "Your share of \"%1$s\" ends automatically on %2$s. The file itself is not deleted. If the share is still needed, share the file again afterwards.": "La tua condivisione di \"%1$s\" terminerà automaticamente il %2$s. Il file non viene eliminato. Se la condivisione è ancora necessaria, condividi nuovamente il file in seguito."
},
    "nplurals=2; plural=(n != 1);"
);
