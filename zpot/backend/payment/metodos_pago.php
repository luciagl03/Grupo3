<?php
session_start();
require_once '../sesion/conexion.php';

if (!isset($_SESSION['usuario'])) {
    header('Location: ../sesion/login.php');
    exit;
}

$dni = $_SESSION['dni'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Métodos de pago — Zpot</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="../app.css">
    <script src="../translations.js"></script>
    <style>
        /* ── Layout ── */
        .payment-page { background: var(--brand-bg); min-height: 100vh; }
        .layout-container { max-width: 600px; margin: 0 auto; padding: 2rem 1.25rem 4rem; }

        /* ── Header ── */
        .page-header { margin-bottom: 2rem; }
        .back-link { display:inline-flex; align-items:center; gap:0.4rem; font-size:0.875rem; color:var(--text-muted); text-decoration:none; margin-bottom:1.25rem; transition:color 0.15s; }
        .back-link:hover { color:var(--brand-dark); }
        .header-content { display:flex; align-items:center; gap:1rem; }
        .header-icon { width:48px; height:48px; background:var(--brand-yellow); border-radius:12px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
        .header-icon i { color:var(--brand-dark); }
        .headline { font-size:1.5rem; font-weight:800; color:var(--brand-dark); margin:0 0 0.2rem; }
        .support { font-size:0.875rem; color:var(--text-muted); margin:0; }

        /* ── Sección ── */
        .section { margin-bottom:1.5rem; }
        .section-title { font-size:0.7rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--text-muted); margin:0 0 0.6rem 0.25rem; }

        /* ── Tarjetas de método ── */
        .metodos-list { display:flex; flex-direction:column; gap:0.5rem; }
        .metodo-card {
            background:#fff; border:1.5px solid var(--border); border-radius:14px;
            padding:1rem 1.1rem; display:flex; align-items:center; gap:0.9rem;
            transition:border-color 0.15s, box-shadow 0.15s;
            position:relative;
        }
        .metodo-card.defecto {
            border-color:var(--brand-dark);
            box-shadow:0 0 0 3px rgba(58,56,47,0.08);
        }
        .metodo-icon {
            width:40px; height:40px; border-radius:10px; background:var(--brand-bg);
            display:flex; align-items:center; justify-content:center; flex-shrink:0;
        }
        .metodo-icon img { width:24px; height:24px; object-fit:contain; }
        .metodo-info { flex:1; min-width:0; }
        .metodo-alias { font-size:0.9rem; font-weight:700; color:var(--brand-dark); margin:0 0 0.15rem; }
        .metodo-detalle { font-size:0.78rem; color:var(--text-muted); margin:0; }
        .defecto-badge {
            font-size:0.65rem; font-weight:700; background:var(--brand-yellow);
            color:var(--brand-dark); padding:2px 8px; border-radius:999px;
            border:1px solid rgba(58,56,47,0.15); white-space:nowrap;
        }
        .metodo-actions { display:flex; gap:0.4rem; flex-shrink:0; }
        .btn-icon {
            width:32px; height:32px; border-radius:8px; border:1px solid var(--border);
            background:#fff; cursor:pointer; display:flex; align-items:center; justify-content:center;
            transition:background 0.15s, border-color 0.15s; color:var(--text-muted);
        }
        .btn-icon:hover { background:var(--brand-bg); border-color:var(--brand-dark); color:var(--brand-dark); }
        .btn-icon.danger:hover { background:#fee2e2; border-color:#c0392b; color:#c0392b; }

        /* ── Botón añadir ── */
        .btn-add {
            width:100%; padding:0.85rem; border:1.5px dashed var(--border); border-radius:14px;
            background:transparent; color:var(--text-muted); font-family:inherit;
            font-size:0.875rem; font-weight:600; cursor:pointer;
            display:flex; align-items:center; justify-content:center; gap:0.5rem;
            transition:border-color 0.15s, color 0.15s, background 0.15s;
        }
        .btn-add:hover { border-color:var(--brand-dark); color:var(--brand-dark); background:#fff; }

        /* ── Info box ── */
        .info-box {
            background:#fff; border:1px solid var(--border); border-radius:12px;
            padding:0.9rem 1rem; display:flex; gap:0.75rem; align-items:flex-start;
        }
        .info-box i { color:var(--text-muted); flex-shrink:0; margin-top:1px; }
        .info-box p { font-size:0.8rem; color:var(--text-muted); margin:0; line-height:1.5; }

        /* ── Empty state ── */
        .empty-state {
            text-align:center; padding:2.5rem 1rem;
            background:#fff; border-radius:14px; border:1px solid var(--border);
        }
        .empty-state i { color:var(--text-muted); margin-bottom:0.75rem; }
        .empty-state p { font-size:0.875rem; color:var(--text-muted); margin:0; }

        /* ── Modal ── */
        .modal-overlay {
            position:fixed; inset:0; background:rgba(58,56,47,0.45);
            backdrop-filter:blur(4px); z-index:2000;
            display:flex; align-items:flex-end; justify-content:center;
            opacity:0; pointer-events:none; transition:opacity 0.2s;
        }
        .modal-overlay.open { opacity:1; pointer-events:all; }
        .modal-sheet {
            background:#fff; border-radius:20px 20px 0 0; width:100%; max-width:520px;
            padding:1.5rem 1.5rem calc(1.5rem + env(safe-area-inset-bottom));
            transform:translateY(40px); transition:transform 0.25s cubic-bezier(0.34,1.56,0.64,1);
            max-height:90vh; overflow-y:auto;
        }
        .modal-overlay.open .modal-sheet { transform:translateY(0); }
        .modal-handle { width:36px; height:4px; background:var(--border); border-radius:2px; margin:0 auto 1.25rem; }
        .modal-title { font-size:1.1rem; font-weight:800; color:var(--brand-dark); margin:0 0 1.25rem; }

        /* ── Tipo selector ── */
        .tipo-grid { display:grid; grid-template-columns:1fr 1fr; gap:0.5rem; margin-bottom:1.25rem; }
        .tipo-btn {
            padding:0.85rem; border:1.5px solid var(--border); border-radius:12px;
            background:#fff; cursor:pointer; display:flex; flex-direction:column;
            align-items:center; gap:0.4rem; transition:border-color 0.15s, background 0.15s;
            font-family:inherit;
        }
        .tipo-btn.active { border-color:var(--brand-dark); background:var(--brand-bg); }
        .tipo-btn span { font-size:0.8rem; font-weight:600; color:var(--brand-dark); }

        /* ── Formulario ── */
        .form-group { margin-bottom:0.9rem; }
        .form-label { display:block; font-size:0.78rem; font-weight:600; color:var(--text-muted); margin-bottom:0.35rem; text-transform:uppercase; letter-spacing:0.04em; }
        .form-input {
            width:100%; padding:0.65rem 0.875rem; border:1.5px solid var(--border);
            border-radius:10px; font-family:inherit; font-size:0.9rem; color:var(--brand-dark);
            background:#fff; box-sizing:border-box; transition:border-color 0.15s, box-shadow 0.15s;
        }
        .form-input:focus { outline:none; border-color:var(--brand-dark); box-shadow:0 0 0 3px rgba(244,221,73,0.3); }
        .form-row { display:grid; grid-template-columns:1fr 1fr; gap:0.75rem; }

        /* Selector de marca */
        .marca-grid { display:flex; gap:0.4rem; flex-wrap:wrap; }
        .marca-btn {
            padding:0.4rem 0.75rem; border:1.5px solid var(--border); border-radius:8px;
            background:#fff; cursor:pointer; font-size:0.78rem; font-weight:600;
            color:var(--text-muted); font-family:inherit; transition:all 0.15s;
        }
        .marca-btn.active { border-color:var(--brand-dark); background:var(--brand-dark); color:#fff; }

        /* Checkbox defecto */
        .check-row { display:flex; align-items:center; gap:0.6rem; margin-bottom:1.25rem; cursor:pointer; }
        .check-row input { width:16px; height:16px; accent-color:var(--brand-dark); cursor:pointer; }
        .check-row span { font-size:0.875rem; color:var(--brand-dark); font-weight:500; }

        /* Botones modal */
        .modal-actions { display:flex; gap:0.5rem; margin-top:0.5rem; }
        .btn-primary-full {
            flex:1; padding:0.85rem; background:var(--brand-dark); color:var(--brand-yellow);
            border:none; border-radius:12px; font-family:inherit; font-weight:700;
            font-size:0.9rem; cursor:pointer; transition:background 0.15s;
        }
        .btn-primary-full:hover { background:#1a1915; }
        .btn-cancel {
            padding:0.85rem 1.1rem; background:var(--brand-bg); color:var(--brand-dark);
            border:1px solid var(--border); border-radius:12px; font-family:inherit;
            font-weight:600; font-size:0.9rem; cursor:pointer; transition:background 0.15s;
        }
        .btn-cancel:hover { background:var(--border); }

        /* Error/success msg */
        .form-msg { font-size:0.8rem; margin-top:0.5rem; min-height:1rem; }
        .form-msg.error { color:#c0392b; }
        .form-msg.ok    { color:#065f46; }

        /* Números de tarjeta con formato */
        .card-number-preview {
            font-size:1.1rem; font-weight:700; letter-spacing:0.15em;
            color:var(--brand-dark); padding:0.5rem 0 0.25rem;
        }

        @media (min-width:480px) {
            .modal-overlay { align-items:center; }
            .modal-sheet { border-radius:20px; margin:1rem; }
        }
    </style>
</head>
<body class="payment-page">
<div class="layout">
    <div class="layout-container">

        <header class="page-header">
            <a href="../index.php" class="back-link">
                <i data-lucide="arrow-left"></i> <span data-i18n="backToMap">Volver al mapa</span>
            </a>
            <div class="header-content">
                <div class="header-icon"><i data-lucide="credit-card" width="22" height="22"></i></div>
                <div>
                    <h1 class="headline" data-i18n="paymentMethodsTitle">Métodos de pago</h1>
                    <p class="support" data-i18n="paymentMethodsSubtitle">Gestiona tus formas de pago para futuras reservas</p>
                </div>
            </div>
        </header>

        <!-- Lista de métodos -->
        <div class="section">
            <p class="section-title" data-i18n="savedMethodsTitle">Tus métodos guardados</p>
            <div id="metodosList" class="metodos-list">
                <div class="empty-state">
                    <i data-lucide="credit-card" width="32" height="32"></i>
                    <p style="margin-top:0.5rem;" data-i18n="loadingMethods">Cargando métodos de pago…</p>
                </div>
            </div>
        </div>

        <!-- Botones añadir -->
        <div class="section">
            <p class="section-title" data-i18n="addMethodTitle">Añadir método</p>
            <div style="display:flex;flex-direction:column;gap:0.5rem;">
                <button class="btn-add" onclick="abrirModal('paypal')">
                    <i data-lucide="plus" width="16" height="16"></i>
                    <span data-i18n="addPayPalButton">Añadir cuenta de PayPal</span>
                </button>
                <button class="btn-add" onclick="abrirModal('tarjeta')">
                    <i data-lucide="plus" width="16" height="16"></i>
                    <span data-i18n="addCardButton">Añadir tarjeta de crédito / débito</span>
                </button>
            </div>
        </div>

        <!-- Info seguridad -->
        <div class="section">
            <div class="info-box">
                <i data-lucide="shield-check" width="16" height="16"></i>
                <p data-i18n="securityInfo">Tus datos de pago están protegidos. Los números de tarjeta son procesados de forma segura por PayPal y nunca se almacenan en nuestros servidores. Solo guardamos los últimos 4 dígitos como referencia visual.</p>
            </div>
        </div>

    </div>
</div>

<!-- ── Modal añadir método ── -->
<div class="modal-overlay" id="modalOverlay">
    <div class="modal-sheet" id="modalSheet">
        <div class="modal-handle"></div>
        <h2 class="modal-title" id="modalTitle" data-i18n="addPaymentMethodTitle">Añadir método de pago</h2>

        <!-- PayPal form -->
        <div id="formPaypal">
            <div class="form-group">
                <label class="form-label" data-i18n="aliasLabel">Alias (nombre que verás)</label>
                <input type="text" class="form-input" id="pp_alias" data-i18n="aliasPayPalPlaceholder" placeholder="Ej: Mi PayPal personal" maxlength="80">
            </div>
            <div class="form-group">
                <label class="form-label" data-i18n="paypalEmailLabel">Email de PayPal</label>
                <input type="email" class="form-input" id="pp_email" data-i18n="emailPlaceholder" placeholder="ejemplo@email.com">
            </div>
            <label class="check-row">
                <input type="checkbox" id="pp_defecto">
                <span data-i18n="setAsDefaultLabel">Establecer como método predeterminado</span>
            </label>
            <p class="form-msg" id="pp_msg"></p>
            <div class="modal-actions">
                <button class="btn-cancel" onclick="cerrarModal()" data-i18n="cancelButton">Cancelar</button>
                <button class="btn-primary-full" onclick="guardarPaypal()">
                    <i data-lucide="save" width="15" height="15" style="display:inline;vertical-align:middle;margin-right:4px;"></i>
                    <span data-i18n="saveButton">Guardar</span>
                </button>
            </div>
        </div>

        <!-- Tarjeta form -->
        <div id="formTarjeta" hidden>
            <div class="form-group">
                <label class="form-label" data-i18n="aliasLabel">Alias (nombre que verás)</label>
                <input type="text" class="form-input" id="tc_alias" data-i18n="aliasCardPlaceholder" placeholder="Ej: Visa personal" maxlength="80">
            </div>

            <div class="form-group">
                <label class="form-label" data-i18n="cardBrandLabel">Marca de la tarjeta</label>
                <div class="marca-grid">
                    <button class="marca-btn" data-marca="visa"       onclick="seleccionarMarca(this)">Visa</button>
                    <button class="marca-btn" data-marca="mastercard" onclick="seleccionarMarca(this)">Mastercard</button>
                    <button class="marca-btn" data-marca="amex"       onclick="seleccionarMarca(this)">Amex</button>
                    <button class="marca-btn" data-marca="otra"       onclick="seleccionarMarca(this)" data-i18n="otherBrandButton">Otra</button>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" data-i18n="last4DigitsLabel">Últimos 4 dígitos</label>
                <input type="text" class="form-input" id="tc_ultimos4" placeholder="•••• •••• •••• 0000"
                       maxlength="4" inputmode="numeric" pattern="\d{4}"
                       oninput="this.value=this.value.replace(/\D/g,'')">
                <p style="font-size:0.72rem;color:var(--text-muted);margin:0.3rem 0 0;" data-i18n="last4DigitsHint">Solo los últimos 4 dígitos — nunca almacenamos el número completo.</p>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" data-i18n="expiryLabel">Caducidad</label>
                    <input type="text" class="form-input" id="tc_caducidad" placeholder="MM/AA"
                           maxlength="5" inputmode="numeric"
                           oninput="formatCaducidad(this)">
                </div>
            </div>

            <label class="check-row">
                <input type="checkbox" id="tc_defecto">
                <span data-i18n="setAsDefaultLabel">Establecer como método predeterminado</span>
            </label>
            <p class="form-msg" id="tc_msg"></p>
            <div class="modal-actions">
                <button class="btn-cancel" onclick="cerrarModal()" data-i18n="cancelButton">Cancelar</button>
                <button class="btn-primary-full" onclick="guardarTarjeta()">
                    <i data-lucide="save" width="15" height="15" style="display:inline;vertical-align:middle;margin-right:4px;"></i>
                    <span data-i18n="saveButton">Guardar</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
lucide.createIcons();

var API = '../payment/metodos_pago_api.php';
var marcaSeleccionada = '';

// ── Iconos SVG por marca ─────────────────────
var marcaIconos = {
    visa:       '<svg viewBox="0 0 48 48" width="28" height="18"><rect width="48" height="48" rx="4" fill="#1A1F71"/><text x="50%" y="62%" font-size="18" font-weight="bold" fill="#F7A600" text-anchor="middle" font-family="Arial">VISA</text></svg>',
    mastercard: '<svg viewBox="0 0 48 30" width="32" height="20"><circle cx="18" cy="15" r="12" fill="#EB001B"/><circle cx="30" cy="15" r="12" fill="#F79E1B" opacity="0.85"/></svg>',
    amex:       '<svg viewBox="0 0 48 48" width="28" height="18"><rect width="48" height="48" rx="4" fill="#2E77BC"/><text x="50%" y="62%" font-size="11" font-weight="bold" fill="white" text-anchor="middle" font-family="Arial">AMEX</text></svg>',
    paypal:     '<svg viewBox="0 0 24 24" width="20" height="20" fill="none"><path d="M7 4h8a4 4 0 0 1 0 8H9l-2 8H4L7 4z" fill="#003087"/><path d="M11 8h5a4 4 0 0 1 0 8H10l-1 4H6l4-12z" fill="#009cde"/></svg>',
    otra:       ''
};

function getMarcaIcon(marca) {
    return marcaIconos[marca] || '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>';
}

// ── Cargar métodos ───────────────────────────
function cargarMetodos() {
    fetch(API, { credentials: 'same-origin' })
        .then(function(r){ return r.json(); })
        .then(function(data) {
            if (!data.success) return;
            renderMetodos(data.metodos);
        })
        .catch(function(){});
}

function renderMetodos(metodos) {
    var list = document.getElementById('metodosList');
    if (!metodos || metodos.length === 0) {
        var currentLang = getCurrentLanguage();
        var emptyMsg = t('noPaymentMethods', currentLang);
        list.innerHTML = '<div class="empty-state"><i data-lucide="credit-card" width="32" height="32"></i><p style="margin-top:0.5rem;">' + emptyMsg + '</p></div>';
        lucide.createIcons();
        return;
    }

    list.innerHTML = metodos.map(function(m) {
        var icono = m.tipo === 'paypal' ? getMarcaIcon('paypal') : getMarcaIcon(m.marca || 'otra');
        var detalle = m.tipo === 'paypal'
            ? m.email_paypal
            : (m.marca ? m.marca.charAt(0).toUpperCase() + m.marca.slice(1) : 'Tarjeta') + ' •••• ' + (m.ultimos4 || '????') + (m.caducidad ? ' · ' + m.caducidad : '');

        var currentLang = getCurrentLanguage();
        var defaultBadge = t('defaultBadge', currentLang);
        var setDefaultTitle = t('setAsDefaultTitle', currentLang);
        var deleteTitle = t('deleteTitle', currentLang);
        
        return '<div class="metodo-card' + (m.es_defecto ? ' defecto' : '') + '">' +
            '<div class="metodo-icon">' + icono + '</div>' +
            '<div class="metodo-info">' +
                '<p class="metodo-alias">' + esc(m.alias) + '</p>' +
                '<p class="metodo-detalle">' + esc(detalle) + '</p>' +
            '</div>' +
            (m.es_defecto ? '<span class="defecto-badge">' + defaultBadge + '</span>' : '') +
            '<div class="metodo-actions">' +
                (!m.es_defecto ? '<button class="btn-icon" title="' + setDefaultTitle + '" onclick="setDefecto(' + m.id + ')"><i data-lucide="star" width="14" height="14"></i></button>' : '') +
                '<button class="btn-icon danger" title="' + deleteTitle + '" onclick="eliminarMetodo(' + m.id + ', \'' + esc(m.alias) + '\')"><i data-lucide="trash-2" width="14" height="14"></i></button>' +
            '</div>' +
        '</div>';
    }).join('');
    lucide.createIcons();
}

// ── Modal ────────────────────────────────────
function abrirModal(tipo) {
    document.getElementById('formPaypal').hidden  = tipo !== 'paypal';
    document.getElementById('formTarjeta').hidden = tipo !== 'tarjeta';
    var currentLang = getCurrentLanguage();
    var title = tipo === 'paypal' ? t('addPayPalTitle', currentLang) : t('addCardTitle', currentLang);
    document.getElementById('modalTitle').textContent = title;
    limpiarForm();
    document.getElementById('modalOverlay').classList.add('open');
}
function cerrarModal() {
    document.getElementById('modalOverlay').classList.remove('open');
}
document.getElementById('modalOverlay').addEventListener('click', function(e) {
    if (e.target === this) cerrarModal();
});

function limpiarForm() {
    ['pp_alias','pp_email','tc_alias','tc_ultimos4','tc_caducidad'].forEach(function(id){
        var el = document.getElementById(id);
        if (el) el.value = '';
    });
    ['pp_defecto','tc_defecto'].forEach(function(id){
        var el = document.getElementById(id);
        if (el) el.checked = false;
    });
    ['pp_msg','tc_msg'].forEach(function(id){
        var el = document.getElementById(id);
        if (el) { el.textContent = ''; el.className = 'form-msg'; }
    });
    document.querySelectorAll('.marca-btn').forEach(function(b){ b.classList.remove('active'); });
    marcaSeleccionada = '';
}

function seleccionarMarca(btn) {
    document.querySelectorAll('.marca-btn').forEach(function(b){ b.classList.remove('active'); });
    btn.classList.add('active');
    marcaSeleccionada = btn.dataset.marca;
}

function formatCaducidad(input) {
    var v = input.value.replace(/\D/g,'');
    if (v.length >= 2) v = v.slice(0,2) + '/' + v.slice(2,4);
    input.value = v;
}

// ── Guardar PayPal ───────────────────────────
function guardarPaypal() {
    var alias    = document.getElementById('pp_alias').value.trim();
    var email    = document.getElementById('pp_email').value.trim();
    var defecto  = document.getElementById('pp_defecto').checked;
    var msg      = document.getElementById('pp_msg');
    var currentLang = getCurrentLanguage();

    msg.className = 'form-msg';
    if (!alias)  { msg.textContent = t('errorEnterAlias', currentLang);          msg.className = 'form-msg error'; return; }
    if (!email)  { msg.textContent = t('errorEnterPayPalEmail', currentLang); msg.className = 'form-msg error'; return; }

    apiFetch({ accion:'añadir', tipo:'paypal', alias:alias, email_paypal:email, es_defecto:defecto }, function(data) {
        if (data && data.success) {
            cerrarModal();
            cargarMetodos();
        } else {
            var err = (data && data.errors) ? data.errors[0] : (data && data.error) || 'Error al guardar';
            msg.textContent = err;
            msg.className = 'form-msg error';
        }
    });
}

// ── Guardar tarjeta ──────────────────────────
function guardarTarjeta() {
    var alias     = document.getElementById('tc_alias').value.trim();
    var ultimos4  = document.getElementById('tc_ultimos4').value.trim();
    var caducidad = document.getElementById('tc_caducidad').value.trim();
    var defecto   = document.getElementById('tc_defecto').checked;
    var msg       = document.getElementById('tc_msg');
    var currentLang = getCurrentLanguage();

    msg.className = 'form-msg';
    if (!alias)                 { msg.textContent = t('errorEnterAlias', currentLang);            msg.className = 'form-msg error'; return; }
    if (!marcaSeleccionada)     { msg.textContent = t('errorSelectBrand', currentLang);           msg.className = 'form-msg error'; return; }
    if (!/^\d{4}$/.test(ultimos4)) { msg.textContent = t('errorEnterLast4', currentLang); msg.className = 'form-msg error'; return; }
    if (!/^(0[1-9]|1[0-2])\/\d{2}$/.test(caducidad)) { msg.textContent = t('errorInvalidExpiry', currentLang); msg.className = 'form-msg error'; return; }

    apiFetch({ accion:'añadir', tipo:'tarjeta', alias:alias, marca:marcaSeleccionada, ultimos4:ultimos4, caducidad:caducidad, es_defecto:defecto }, function(data) {
        if (data && data.success) {
            cerrarModal();
            cargarMetodos();
        } else {
            var err = (data && data.errors) ? data.errors[0] : (data && data.error) || 'Error al guardar';
            msg.textContent = err;
            msg.className = 'form-msg error';
        }
    });
}

// ── Establecer defecto ───────────────────────
function setDefecto(id) {
    apiFetch({ accion:'defecto', id_metodo:id }, function(data) {
        if (data && data.success) cargarMetodos();
    });
}

// ── Eliminar ─────────────────────────────────
function eliminarMetodo(id, alias) {
    var currentLang = getCurrentLanguage();
    var confirmMsg = t('confirmDeleteMethod', currentLang).replace('{alias}', alias);
    if (!confirm(confirmMsg)) return;
    apiFetch({ accion:'eliminar', id_metodo:id }, function(data) {
        if (data && data.success) cargarMetodos();
    });
}

// ── Fetch helper ─────────────────────────────
function apiFetch(body, cb) {
    fetch(API, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify(body)
    }).then(function(r){ return r.json(); }).then(cb).catch(function(){ cb(null); });
}

function esc(s) {
    return s ? String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;') : '';
}

// ── Arranque ─────────────────────────────────
cargarMetodos();
</script>
</body>
</html>
