OC.L10N.register(
    "sharecleanup",
    {
    "Share Cleanup": "Limpieza de recursos compartidos",
    "Automatically ends shares older than the configured number of days. Shared files are tagged with the end date of the share, and the sharing user is notified at 90 % of the lifetime. Shares with their own expiration date are never ended. The files themselves are never deleted.": "Finaliza automáticamente los recursos compartidos con una antigüedad superior a los días configurados. Los archivos compartidos se etiquetan con la fecha de finalización y se notifica al usuario que comparte al 90 % de su vida útil. Los recursos compartidos con fecha de expiración propia nunca se finalizan. Los archivos en sí no se eliminan.",
    "Maximum share age (days)": "Antigüedad máxima del recurso compartido (días)",
    "Users are notified after {notify} days (90 %), the share ends after {total} days. Shares with their own expiration date are never ended.": "Se notifica a los usuarios después de {notify} días (90 %), el recurso compartido finaliza después de {total} días. Los recursos con fecha de expiración propia nunca se finalizan.",
    "Dry-run mode (only log, end nothing)": "Modo de prueba (solo registrar, no finalizar nada)",
    "Enabled by default. Test with \"occ sharecleanup:run --dry-run\" and check the log, then disable.": "Habilitado por defecto. Pruebe con \"occ sharecleanup:run --dry-run\" y compruebe el registro, luego desactive.",
    "Save": "Guardar",
    "Settings saved.": "Configuración guardada.",
    "Error saving settings: ": "Error al guardar la configuración: ",
    "Please enter a number of days (minimum 1).": "Introduzca un número de días (mínimo 1).",
    "Manual run: occ sharecleanup:run [--days=N] [--dry-run] [--force]": "Ejecución manual: occ sharecleanup:run [--days=N] [--dry-run] [--force]",
    "Share of \"%s\" ends soon": "El recurso compartido de \"%s\" finaliza pronto",
    "Your share of \"%1$s\" ends automatically on %2$s. The file itself is not deleted. If the share is still needed, share the file again afterwards.": "Su recurso compartido de \"%1$s\" finaliza automáticamente el %2$s. El archivo en sí no se elimina. Si todavía se necesita, vuelva a compartirlo después."
},
    "nplurals=2; plural=(n != 1);"
);
