
// Handles: login/register validation, criminal/case validation,
//          AJAX criminal search, image preview, bar chart, helpers

// LOGIN VALIDATION
// =============================================================
function validateLogin(form) {
    var valid = true;
    clearErrors(['loginEmailErr', 'loginPassErr']);

    if (!form.email.value.trim() || !isValidEmail(form.email.value.trim())) {
        showError('loginEmailErr', 'A valid email address is required.');
        valid = false;
    }
    if (!form.password.value) {
        showError('loginPassErr', 'Password is required.');
        valid = false;
    }
    return valid;
}

// REGISTER VALIDATION
// =============================================================
function validateRegister(form) {
    var valid = true;
    clearErrors(['regNameErr', 'regEmailErr', 'regPassErr', 'regConfirmErr', 'regBadgeErr']);

    if (!form.name.value.trim()) {
        showError('regNameErr', 'Full name is required.');
        valid = false;
    }
    if (!form.email.value.trim() || !isValidEmail(form.email.value.trim())) {
        showError('regEmailErr', 'A valid email address is required.');
        valid = false;
    }
    if (form.password.value.length < 8) {
        showError('regPassErr', 'Password must be at least 8 characters.');
        valid = false;
    }
    if (form.password.value !== form.confirm_password.value) {
        showError('regConfirmErr', 'Passwords do not match.');
        valid = false;
    }
    if (!form.badge_number.value.trim()) {
        showError('regBadgeErr', 'Badge number is required.');
        valid = false;
    }
    return valid;
}

// =============================================================
// PASSWORD MATCH (profile page)
// =============================================================
function checkPassMatch() {
    var np  = document.getElementById('newPass');
    var cp  = document.getElementById('confirmPass');
    var err = document.getElementById('confirmPassErr');
    if (np && cp && err) {
        if (np.value !== cp.value) {
            err.textContent = 'Passwords do not match.';
            return false;
        }
        err.textContent = '';
    }
    return true;
}

// =============================================================
// CRIMINAL FORM VALIDATION
// =============================================================
function validateCriminal(form) {
    var valid = true;
    clearErrors(['crimNameErr', 'crimThreatErr', 'crimStatusErr']);

    if (!form.full_name.value.trim()) {
        showError('crimNameErr', 'Full name is required.');
        valid = false;
    }
    if (!form.threat_level.value) {
        showError('crimThreatErr', 'Threat level is required.');
        valid = false;
    }
    if (!form.status.value) {
        showError('crimStatusErr', 'Status is required.');
        valid = false;
    }
    return valid;
}

// =============================================================
// CASE FORM VALIDATION
// =============================================================
function validateCase(form) {
    var valid = true;
    clearErrors(['caseNumErr', 'caseTitleErr']);

    // case_number only required on create (it is disabled on edit)
    var numField = form.case_number;
    if (numField && !numField.disabled && !numField.value.trim()) {
        showError('caseNumErr', 'Case number is required.');
        valid = false;
    }
    if (!form.title.value.trim()) {
        showError('caseTitleErr', 'Case title is required.');
        valid = false;
    }
    return valid;
}

// =============================================================
// AJAX CRIMINAL SEARCH (XMLHttpRequest)
// =============================================================
function ajaxSearch(query) {
    var panel   = document.getElementById('searchPanel');
    var mainTbl = document.getElementById('mainTable');
    if (!panel) return;

    if (!query.trim()) {
        panel.innerHTML   = '';
        if (mainTbl) mainTbl.style.display = 'block';
        return;
    }

    if (mainTbl) mainTbl.style.display = 'none';
    panel.innerHTML = '<p style="color:#546e7a;font-size:.875rem;padding:8px 0">Searching…</p>';

    var xhr = new XMLHttpRequest();
    xhr.onload = function () {
        if (xhr.status === 200) {
            var criminals;
            try { criminals = JSON.parse(xhr.responseText); }
            catch (e) { panel.innerHTML = '<div class="alert alert-danger">Invalid response.</div>'; return; }

            if (criminals.length === 0) {
                panel.innerHTML = '<div class="no-data">No criminals matched "' + escapeHtml(query) + '".</div>';
                return;
            }

            var html = '<div class="card"><div class="table-wrap"><table>'
                     + '<thead><tr><th>Name</th><th>Alias</th><th>Nationality</th><th>Threat</th><th>Status</th><th></th></tr></thead><tbody>';

            for (var i = 0; i < criminals.length; i++) {
                var c = criminals[i];
                html += '<tr>'
                      + '<td><strong>' + escapeHtml(c.full_name)                  + '</strong></td>'
                      + '<td>'         + escapeHtml(c.alias        || '—')        + '</td>'
                      + '<td>'         + escapeHtml(c.nationality  || '—')        + '</td>'
                      + '<td><span class="badge badge-threat-' + escapeHtml(c.threat_level) + '">' + ucfirst(c.threat_level) + '</span></td>'
                      + '<td><span class="badge badge-status-' + escapeHtml(c.status)       + '">' + ucfirst(c.status)       + '</span></td>'
                      + '<td><a href="CriminalController.php?action=view&id=' + encodeURIComponent(c.id) + '" class="btn btn-sm btn-primary">View</a></td>'
                      + '</tr>';
            }
            html += '</tbody></table></div></div>';
            panel.innerHTML = html;
        } else {
            panel.innerHTML = '<div class="alert alert-danger">Search failed (HTTP ' + xhr.status + ').</div>';
        }
    };
    xhr.onerror = function () {
        panel.innerHTML = '<div class="alert alert-danger">Network error. Try again.</div>';
    };
    xhr.open('GET', 'CriminalController.php?action=search_ajax&q=' + encodeURIComponent(query), true);
    xhr.send();
}

// =============================================================
// LIVE TABLE SEARCH (client-side filter)
// =============================================================
function tableSearch(input, tableId) {
    var filter = input.value.toLowerCase();
    var table  = document.getElementById(tableId);
    if (!table) return;
    var rows = table.querySelectorAll('tbody tr');
    for (var i = 0; i < rows.length; i++) {
        rows[i].style.display = rows[i].textContent.toLowerCase().indexOf(filter) !== -1 ? '' : 'none';
    }
}

// =============================================================
// IMAGE PREVIEW (file input)
// =============================================================
function setupImagePreview(inputId, previewId) {
    var input   = document.getElementById(inputId);
    var preview = document.getElementById(previewId);
    if (!input || !preview) return;

    input.addEventListener('change', function () {
        var file = this.files[0];
        if (!file) return;
        var reader = new FileReader();
        reader.onload = function (e) {
            preview.src           = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    });
}

// =============================================================
// BAR CHART (Canvas — cases per month)
// =============================================================
function drawVolumeChart(canvasId, volumeData) {
    var canvas = document.getElementById(canvasId);
    if (!canvas || !volumeData || volumeData.length === 0) return;

    var ctx    = canvas.getContext('2d');
    var labels = volumeData.map(function (d) { return d.day; });
    var values = volumeData.map(function (d) { return parseInt(d.order_count); });
    var maxVal = Math.max.apply(null, values.concat([1]));

    var W      = canvas.parentElement.offsetWidth || 700;
    var H      = 180;
    canvas.width  = W;
    canvas.height = H;

    var padL   = 40;
    var padR   = 16;
    var padT   = 24;
    var padB   = 28;
    var chartW = W - padL - padR;
    var chartH = H - padT - padB;
    var n      = labels.length;
    var step   = chartW / n;
    var barW   = Math.max(6, step - 6);

    // background
    ctx.fillStyle = '#0a1628';
    ctx.fillRect(0, 0, W, H);

    // gridlines + y labels
    ctx.strokeStyle = '#1e3a5f';
    ctx.fillStyle   = '#546e7a';
    ctx.font        = '10px Arial';
    ctx.textAlign   = 'right';
    for (var g = 0; g <= 4; g++) {
        var gy  = padT + chartH - (g / 4) * chartH;
        var gv  = Math.round((g / 4) * maxVal);
        ctx.beginPath();
        ctx.moveTo(padL, gy);
        ctx.lineTo(padL + chartW, gy);
        ctx.stroke();
        ctx.fillText(gv, padL - 4, gy + 4);
    }

    // bars
    for (var i = 0; i < n; i++) {
        var barH = Math.max(2, (values[i] / maxVal) * chartH);
        var x    = padL + i * step + (step - barW) / 2;
        var y    = padT + chartH - barH;

        var grad = ctx.createLinearGradient(x, y, x, padT + chartH);
        grad.addColorStop(0, '#4fc3f7');
        grad.addColorStop(1, '#1565c0');
        ctx.fillStyle = grad;
        ctx.fillRect(x, y, barW, barH);

        // value label
        if (values[i] > 0) {
            ctx.fillStyle = '#e0e0e0';
            ctx.font      = '10px Arial';
            ctx.textAlign = 'center';
            ctx.fillText(values[i], x + barW / 2, y - 4);
        }

        // x label (every nth)
        var everyN = Math.ceil(n / 8);
        if (i % everyN === 0) {
            ctx.fillStyle = '#546e7a';
            ctx.font      = '9px Arial';
            ctx.textAlign = 'center';
            ctx.fillText(labels[i].slice(5), x + barW / 2, H - 6);
        }
    }
}

// =============================================================
// CONFIRM DELETE
// =============================================================
function confirmDelete(msg) {
    return confirm(msg || 'Are you sure? This cannot be undone.');
}

// =============================================================
// HELPERS
// =============================================================
function showError(id, msg) {
    var el = document.getElementById(id);
    if (el) el.textContent = msg;
}

function clearErrors(ids) {
    for (var i = 0; i < ids.length; i++) { showError(ids[i], ''); }
}

function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function escapeHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function ucfirst(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
}

// =============================================================
// DOM READY
// =============================================================
document.addEventListener('DOMContentLoaded', function () {

    // image preview
    setupImagePreview('primaryImageInput', 'primaryImagePreview');
    setupImagePreview('shopLogoInput',     'shopLogoPreview');

    // bar chart
    if (typeof volumeData !== 'undefined') {
        drawVolumeChart('volumeChart', volumeData);
    }

    // auto-dismiss alerts after 5 s
    var alerts = document.querySelectorAll('.alert');
    for (var i = 0; i < alerts.length; i++) {
        (function (a) {
            setTimeout(function () {
                a.style.transition = 'opacity .5s';
                a.style.opacity    = '0';
                setTimeout(function () { a.remove(); }, 500);
            }, 5000);
        })(alerts[i]);
    }

    // active nav highlight
    var links = document.querySelectorAll('.nav-links a');
    for (var j = 0; j < links.length; j++) {
        if (window.location.href.indexOf(links[j].getAttribute('href')) !== -1) {
            links[j].classList.add('active');
        }
    }
});