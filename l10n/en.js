OC.L10N.register(
    "sharecleanup",
    {
    "Share Cleanup": "Share Cleanup",
    "Automatically ends shares older than the configured number of days. Shared files are tagged with the end date of the share, and the sharing user is notified at 90 % of the lifetime. Shares with their own expiration date are never ended. The files themselves are never deleted.": "Automatically ends shares older than the configured number of days. Shared files are tagged with the end date of the share, and the sharing user is notified at 90 % of the lifetime. Shares with their own expiration date are never ended. The files themselves are never deleted.",
    "Maximum share age (days)": "Maximum share age (days)",
    "Users are notified after {notify} days (90 %), the share ends after {total} days. Shares with their own expiration date are never ended.": "Users are notified after {notify} days (90 %), the share ends after {total} days. Shares with their own expiration date are never ended.",
    "Dry-run mode (only log, end nothing)": "Dry-run mode (only log, end nothing)",
    "Enabled by default. Test with \"occ sharecleanup:run --dry-run\" and check the log, then disable.": "Enabled by default. Test with \"occ sharecleanup:run --dry-run\" and check the log, then disable.",
    "Save": "Save",
    "Settings saved.": "Settings saved.",
    "Error saving settings: ": "Error saving settings: ",
    "Please enter a number of days (minimum 1).": "Please enter a number of days (minimum 1).",
    "Manual run: occ sharecleanup:run [--days=N] [--dry-run] [--force]": "Manual run: occ sharecleanup:run [--days=N] [--dry-run] [--force]",
    "Share of \"%s\" ends soon": "Share of \"%s\" ends soon",
    "Your share of \"%1$s\" ends automatically on %2$s. The file itself is not deleted. If the share is still needed, share the file again afterwards.": "Your share of \"%1$s\" ends automatically on %2$s. The file itself is not deleted. If the share is still needed, share the file again afterwards."
},
    "nplurals=2; plural=(n != 1);"
);
