(function() {
    'use strict';

    var state = OCP.InitialState.loadState('sharecleanup', 'adminSettings');

    var container = document.getElementById('sharecleanup-settings');
    if (!container) {
        return;
    }

    var savedTimer = null;

    function updatePreview() {
        var days = parseInt(daysInput.value, 10);
        var preview = container.querySelector('#sc-notify-preview');
        if (!isNaN(days) && days >= 1) {
            var notifyDays = Math.max(1, Math.floor(days * 0.9));
            preview.textContent = t('sharecleanup',
                'Users are notified after {notify} days (90 %), the share ends after {total} days. Shares with their own expiration date are never ended.'
            ).replace('{notify}', notifyDays).replace('{total}', days);
        } else {
            preview.textContent = '';
        }
    }

    function save() {
        var days = parseInt(daysInput.value, 10);
        if (isNaN(days) || days < 1) {
            daysInput.setCustomValidity(t('sharecleanup', 'Please enter a number of days (minimum 1).'));
            daysInput.reportValidity();
            return;
        }
        daysInput.setCustomValidity('');

        fetch(OC.generateUrl('/apps/sharecleanup/settings/admin'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'OCS-APIREQUEST': 'true',
                'requesttoken': OC.requestToken
            },
            body: JSON.stringify({ maxAgeDays: days, dryRun: dryRunInput.checked })
        }).then(function(resp) {
            if (!resp.ok) {
                throw new Error('HTTP ' + resp.status);
            }
            savedMsg.textContent = t('sharecleanup', 'Settings saved.');
            savedMsg.style.color = 'var(--color-success, #46ba61)';
            clearTimeout(savedTimer);
            savedTimer = setTimeout(function() { savedMsg.textContent = ''; }, 4000);
        }).catch(function(err) {
            savedMsg.textContent = t('sharecleanup', 'Error saving settings: ') + err.message;
            savedMsg.style.color = 'var(--color-error, #e9322d)';
        });
    }

    container.innerHTML =
        '<div class="section">' +
            '<h2>' + t('sharecleanup', 'Share Cleanup') + '</h2>' +
            '<p class="settings-hint">' + t('sharecleanup',
                'Automatically ends shares older than the configured number of days. Shared files are tagged with the end date of the share, and the sharing user is notified at 90 % of the lifetime. Shares with their own expiration date are never ended. The files themselves are never deleted.') + '</p>' +
            '<p><label for="sc-max-age"><strong>' + t('sharecleanup', 'Maximum share age (days)') + '</strong></label><br>' +
            '<input id="sc-max-age" type="number" min="1" max="3650" style="width:120px"></p>' +
            '<p id="sc-notify-preview" class="settings-hint"></p>' +
            '<p><label><input id="sc-dry-run" type="checkbox"> ' +
            t('sharecleanup', 'Dry-run mode (only log, end nothing)') + '</label><br>' +
            '<em class="settings-hint">' + t('sharecleanup',
                'Enabled by default. Test with "occ sharecleanup:run --dry-run" and check the log, then disable.') + '</em></p>' +
            '<p><button id="sc-save" class="button primary">' + t('sharecleanup', 'Save') + '</button> ' +
            '<span id="sc-saved"></span></p>' +
            '<p class="settings-hint">' + t('sharecleanup',
                'Manual run: occ sharecleanup:run [--days=N] [--dry-run] [--force]') + '</p>' +
        '</div>';

    var daysInput = container.querySelector('#sc-max-age');
    var dryRunInput = container.querySelector('#sc-dry-run');
    var saveBtn = container.querySelector('#sc-save');
    var savedMsg = container.querySelector('#sc-saved');

    daysInput.value = state.maxAgeDays;
    dryRunInput.checked = !!state.dryRun;

    daysInput.addEventListener('input', updatePreview);
    saveBtn.addEventListener('click', save);
    updatePreview();
})();
