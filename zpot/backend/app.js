(function () {
    // -----------------------------
    // App constants and shared state
    // -----------------------------
    var MALAGA = [36.7213, -4.4214];
    var DEFAULT_ZOOM = 14;

    var map = null;
    var markersLayer = null;
    var plazas = [];
    var allPlazas = [];
    var currentUser = null;

    var detailPanel = document.getElementById('detailPanel');
    var detailClose = document.getElementById('detailClose');
    var detailPhoto = document.getElementById('detailPhoto');
    var detailPhotoPlaceholder = document.getElementById('detailPhotoPlaceholder');
    var detailTitle = document.getElementById('detailTitle');
    var detailPrice = document.getElementById('detailPrice');
    var detailOwner = document.getElementById('detailOwner');
    var detailDescription = document.getElementById('detailDescription');
    var detailSize = document.getElementById('detailSize');
    var detailBook = document.getElementById('detailBook');

    var accountBtn = document.getElementById('accountBtn');
    var accountDropdown = document.getElementById('accountDropdown');
    var filtersBtn = document.getElementById('filtersBtn');
    var filtersDropdown = document.getElementById('filtersDropdown');

    // Build same-origin API paths relative to backend root.
    function apiUrl(path) {
        // Remove leading slash from path to avoid duplication
        var cleanPath = path.replace(/^\/+/, '');
        return './' + cleanPath;
    }

    // Generic API fetch wrapper:
    // if session is missing, redirect to login and reject.
    function fetchJsonOrRedirect(path) {
        return fetch(apiUrl(path), { credentials: 'same-origin' })
            .then(function (r) {
                if (r.status === 401) {
                    window.location.href = apiUrl('/sesion/login.php');
                    return Promise.reject(new Error('Not authenticated'));
                }
                return r.json();
            });
    }

    // Validate active session before loading map data.
    function checkAuth() {
        return fetchJsonOrRedirect('/sesion/me_api.php');
    }

    // Load published parking spots used to render markers.
    function loadPlazas() {
        return fetchJsonOrRedirect('/parking/plazas_api.php')
            .then(function (data) {
                allPlazas = data.plazas || [];
                plazas = allPlazas;
                return plazas;
            });
    }

    // Returns [lat, lng] from geocoded coords stored in DB, or null if not available.
    function getMarkerPosition(plaza) {
        if (plaza.lat != null && plaza.lng != null) {
            return [plaza.lat, plaza.lng];
        }
        return null;
    }

    function createParkingIcon(precio) {
        var label = (precio != null && precio > 0) ? parseFloat(precio.toFixed(1)) + '€' : 'P';
        var fontSize = label.length > 4 ? '10px' : label.length > 3 ? '11px' : '13px';
        var html = '<div style="width:44px;height:44px;background:#f4dd49;color:#3a382f;border:2px solid #3a382f;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:' + fontSize + ';font-family:sans-serif;box-shadow:0 2px 8px rgba(0,0,0,0.25);">' + label + '</div>';
        return L.divIcon({
            className: 'zpot-parking-marker',
            html: html,
            iconSize: [44, 44],
            iconAnchor: [22, 22]
        });
    }

    // Create map, base layer and marker container.
    function initMap(center) {
        map = L.map('map', {
            center: center,
            zoom: DEFAULT_ZOOM,
            zoomControl: false
        });
        L.control.zoom({ position: 'topright' }).addTo(map);

        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager_labels_under/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/attributions">CARTO</a>',
            subdomains: 'abcd',
            maxZoom: 20
        }).addTo(map);

        markersLayer = L.layerGroup().addTo(map);
        map.on('click', closeDetail);
    }

    // Draw all markers. Pass skipFit=true to keep current viewport (e.g. after filter/geocode).
    function addMarkers(skipFit) {
        if (!markersLayer) return;
        markersLayer.clearLayers();
        var noResults = document.getElementById('noResultsMsg');
        if (!plazas.length) {
            if (noResults) noResults.hidden = false;
            return;
        }
        if (noResults) noResults.hidden = true;
        var bounds = [];
        plazas.forEach(function (p) {
            var pos = getMarkerPosition(p);
            if (!pos) return;
            bounds.push(pos);
            var marker = L.marker(pos, { icon: createParkingIcon(p.precio) });
            marker.on('click', function () { openDetail(p); });
            marker.bindTooltip(p.direccion || 'Plaza', {
                direction: 'top',
                offset: [0, -20],
                opacity: 0.9
            });
            markersLayer.addLayer(marker);
        });
        if (!skipFit && bounds.length > 0) {
            map.fitBounds(bounds, { padding: [40, 40], maxZoom: 15 });
        }
    }

    // Fill and open right-side detail panel for selected plaza.
    function openDetail(plaza) {
        detailTitle.textContent = plaza.direccion || 'Plaza #' + plaza.id;
        detailPrice.textContent = 'Precio: ' + ((plaza.precio != null && plaza.precio > 0)
            ? plaza.precio.toFixed(2) + ' €/h'
            : 'no especificado');
        detailPrice.hidden = false;
        detailOwner.textContent = 'Publicado por ' + (plaza.owner || '—');
        detailDescription.textContent = plaza.descripcion || '';
        detailSize.textContent = (plaza.ancho != null && plaza.largo != null)
            ? plaza.ancho + ' m × ' + plaza.largo + ' m'
            : '';
        if (plaza.foto) {
            detailPhoto.src = plaza.foto;
            detailPhoto.alt = plaza.direccion || 'Plaza';
            detailPhoto.style.display = '';
            detailPhotoPlaceholder.style.display = 'none';
        } else {
            detailPhoto.removeAttribute('src');
            detailPhoto.style.display = 'none';
            detailPhotoPlaceholder.style.display = 'block';
        }
        var tagsEl = document.getElementById('detailTags');
        if (tagsEl) {
            tagsEl.innerHTML = '';
            var ubicLabels = { cubierto: 'Cubierto', garaje: 'Garaje', exterior: 'Exterior' };
            var extraLabels = { ev: 'Carga electrica', vigilado: 'Vigilado', '24h': 'Acceso 24h' };
            if (plaza.ubicacion && ubicLabels[plaza.ubicacion]) {
                var t = document.createElement('span');
                t.className = 'detail-tag detail-tag--type';
                t.textContent = ubicLabels[plaza.ubicacion];
                tagsEl.appendChild(t);
            }
            (plaza.extras || []).forEach(function (e) {
                if (!extraLabels[e]) return;
                var t = document.createElement('span');
                t.className = 'detail-tag';
                t.textContent = extraLabels[e];
                tagsEl.appendChild(t);
            });
        }
        // Cargar reseñas en el widget
        initResenas(plaza.id);

        var isOwner = currentUser && plaza.owner_dni && plaza.owner_dni === currentUser.dni;
        if (isOwner) {
            detailBook.hidden = true;
        } else {
            detailBook.hidden = false;
            detailBook.href = apiUrl('/parking/reserva.php?id_plaza=' + plaza.id);
            detailBook.textContent = 'Reservar';
            detailBook.dataset.plazaId = plaza.id;
        }
        detailPanel.hidden = false;
    }

    // Hide detail panel.
    function closeDetail() {
        detailPanel.hidden = true;
    }

    // Try user geolocation to center map near current position.
    function tryGeolocation() {
        if (!navigator.geolocation) return Promise.resolve(null);
        return new Promise(function (resolve) {
            navigator.geolocation.getCurrentPosition(
                function (pos) {
                    resolve([pos.coords.latitude, pos.coords.longitude]);
                },
                function () { resolve(null); },
                { timeout: 5000, maximumAge: 60000 }
            );
        });
    }

    // Shared dropdown toggler for account/filters menus.
    function toggleDropdown(dropdown, btn, open) {
        var isOpen = open !== undefined ? open : dropdown.hidden;
        dropdown.hidden = !isOpen;
        if (btn) btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    }

    // -----------------------------
    // UI events
    // -----------------------------
    // Filter markers by ubicacion/extras only (does not affect map viewport).
    function applyFilters() {
        var ubicaciones = Array.from(document.querySelectorAll('input[name="filter-ubicacion"]:checked')).map(function (cb) { return cb.value; });
        var extras = Array.from(document.querySelectorAll('input[name="filter-extras"]:checked')).map(function (cb) { return cb.value; });
        plazas = allPlazas.filter(function (p) {
            var matchUbic = ubicaciones.length === 0 || ubicaciones.indexOf(p.ubicacion) !== -1;
            var matchExtras = extras.every(function (e) { return p.extras && p.extras.indexOf(e) !== -1; });
            return matchUbic && matchExtras;
        });
        addMarkers(true); // keep current viewport when filters change
    }

    // Geocode the search query and navigate the map using the result bounding box.
    // This gives natural zoom levels: a country zooms out to show it all,
    // a street zooms in to a neighbourhood — without hard-coding zoom levels.
    var searchTimeout = null;
    function geocodeAndFly(query) {
        query = (query || '').trim();
        if (query.length < 2) return;
        var url = 'https://nominatim.openstreetmap.org/search?format=json&limit=1&countrycodes=es&q=' + encodeURIComponent(query);
        fetch(url, { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (results) {
                if (!results || !results.length) return;
                var r = results[0];
                if (r.boundingbox) {
                    // boundingbox = [south, north, west, east]
                    var bb = r.boundingbox;
                    var bounds = [
                        [parseFloat(bb[0]), parseFloat(bb[2])],
                        [parseFloat(bb[1]), parseFloat(bb[3])]
                    ];
                    map.fitBounds(bounds, { padding: [40, 40], maxZoom: 17, animate: true });
                } else {
                    map.flyTo([parseFloat(r.lat), parseFloat(r.lon)], 14, { duration: 1.2 });
                }
            })
            .catch(function () {});
    }

    var searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                clearTimeout(searchTimeout);
                geocodeAndFly(searchInput.value);
            }
        });
        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function () { geocodeAndFly(searchInput.value); }, 700);
        });
    }

    detailClose.addEventListener('click', closeDetail);

    accountBtn.addEventListener('click', function () {
        toggleDropdown(accountDropdown, accountBtn);
        toggleDropdown(filtersDropdown, filtersBtn, false);
    });
    filtersBtn.addEventListener('click', function () {
        toggleDropdown(filtersDropdown, filtersBtn);
        toggleDropdown(accountDropdown, accountBtn, false);
    });

    document.addEventListener('click', function (e) {
        if (!accountDropdown.contains(e.target) && !accountBtn.contains(e.target)) {
            toggleDropdown(accountDropdown, accountBtn, false);
        }
        if (!filtersDropdown.contains(e.target) && !filtersBtn.contains(e.target)) {
            toggleDropdown(filtersDropdown, filtersBtn, false);
        }
    });

    document.getElementById('clearFilters').addEventListener('click', function () {
        document.querySelectorAll('.filters-dropdown input[type="checkbox"]').forEach(function (cb) { cb.checked = false; });
        applyFilters();
    });
    document.getElementById('applyFilters').addEventListener('click', function () {
        applyFilters();
        toggleDropdown(filtersDropdown, filtersBtn, false);
    });

    // Show one-time banner after creating a plaza (success or geocoding warning).
    (function showPlazaCreatedBannerIfNeeded() {
        var params = new URLSearchParams(window.location.search);
        if (params.get('plaza_created') !== '1') return;
        var bannerId = params.get('sin_ubicacion') === '1' ? 'sinUbicacionBanner' : 'plazaCreatedBanner';
        var banner = document.getElementById(bannerId);
        if (banner) {
            banner.hidden = false;
            setTimeout(function () { banner.hidden = true; }, 7000);
        }
        var cleanUrl = window.location.pathname + (window.location.hash || '');
        if (window.history.replaceState) {
            window.history.replaceState(null, '', cleanUrl);
        }
    })();

    // -----------------------------
    // Boot sequence
    // -----------------------------
    checkAuth()
        .then(function (user) {
            currentUser = user;
            return tryGeolocation();
        })
        .then(function (userPos) {
            var center = userPos || MALAGA;
            initMap(center);
            return loadPlazas();
        })
        .then(function () {
            setTimeout(function () {
                addMarkers();
            }, 100);
        })
        .catch(function () {
            // Intentionally silent: auth redirect or transient API issue.
        });
    // ─────────────────────────────────────────────────────
    // RESEÑAS
    // ─────────────────────────────────────────────────────

    function initResenas(idPlaza) {
        var container = document.getElementById('resenas-widget');
        if (!container) return;

        container.innerHTML =
            '<div class="resenas-section" id="rs-' + idPlaza + '">' +
                '<div class="resenas-header">' +
                    '<h3>Reseñas</h3>' +
                    '<span class="resenas-media-badge" id="rs-media-' + idPlaza + '" style="display:none"></span>' +
                    '<span class="resenas-total" id="rs-total-' + idPlaza + '"></span>' +
                '</div>' +
                '<div class="resenas-list" id="rs-list-' + idPlaza + '">' +
                    '<p class="r-empty">Cargando…</p>' +
                '</div>' +
                '<div id="rs-accion-' + idPlaza + '"></div>' +
                '<div class="r-form" id="rs-form-' + idPlaza + '">' +
                    '<label class="r-form-label">Tu puntuación</label>' +
                    '<div class="r-picker" id="rs-picker-' + idPlaza + '">' +
                        '<span data-val="1">★</span><span data-val="2">★</span><span data-val="3">★</span><span data-val="4">★</span><span data-val="5">★</span>' +
                    '</div>' +
                    '<label class="r-form-label">Comentario (opcional)</label>' +
                    '<textarea id="rs-comment-' + idPlaza + '" maxlength="500" placeholder="Cuéntanos tu experiencia…"></textarea>' +
                    '<div class="r-form-actions">' +
                        '<button class="btn-r-cancelar" onclick="cerrarFormResena(' + idPlaza + ')">Cancelar</button>' +
                        '<button class="btn-r-enviar"   onclick="enviarResena(' + idPlaza + ')">Publicar</button>' +
                    '</div>' +
                    '<p class="r-msg" id="rs-msg-' + idPlaza + '"></p>' +
                '</div>' +
            '</div>';

        _initPicker(idPlaza);
        _cargarResenas(idPlaza);
    }

    function _cargarResenas(idPlaza) {
        fetch('./reseñas/resenas_api.php?id_plaza=' + idPlaza, { credentials: 'same-origin' })
            .then(function(r) { return r.json(); })
            .then(function(data) { _pintarResenas(idPlaza, data); })
            .catch(function() {
                var list = document.getElementById('rs-list-' + idPlaza);
                if (list) list.innerHTML = '<p class="r-empty">No se pudieron cargar las reseñas.</p>';
            });
    }

    function _pintarResenas(idPlaza, data) {
        var list   = document.getElementById('rs-list-'   + idPlaza);
        var media  = document.getElementById('rs-media-'  + idPlaza);
        var total  = document.getElementById('rs-total-'  + idPlaza);
        var accion = document.getElementById('rs-accion-' + idPlaza);

        if (data.total > 0 && media) {
            media.style.display = 'inline-flex';
            media.innerHTML = _stars(data.media, true) + ' ' + data.media;
            if (total) total.textContent = data.total + ' reseña' + (data.total !== 1 ? 's' : '');
        } else if (total) {
            total.textContent = 'Sin reseñas aún';
        }

        if (list) {
            if (!data.resenas || data.resenas.length === 0) {
                list.innerHTML = '<p class="r-empty">¡Sé el primero en opinar!</p>';
            } else {
                list.innerHTML = data.resenas.map(function(r) {
                    return '<div class="r-item">' +
                        '<div class="r-item-top">' +
                            _stars(r.puntuacion, false) +
                            '<span class="r-autor">' + _esc(r.autor) + '</span>' +
                            '<span class="r-fecha">'  + _esc(r.fecha) + '</span>' +
                        '</div>' +
                        (r.comentario ? '<p class="r-comentario">' + _esc(r.comentario) + '</p>' : '') +
                    '</div>';
                }).join('');
            }
        }

        if (!accion) return;
        if (data.puede_resenar) {
            accion.innerHTML = '<button class="btn-escribir-resena" onclick="abrirFormResena(' + idPlaza + ')">&#9998; Escribir una reseña</button>';
        } else if (data.ya_reseno) {
            accion.innerHTML = '<p class="r-empty">Ya has reseñado esta plaza.</p>';
        } else {
            accion.innerHTML = '<p class="r-empty">Solo pueden reseñar usuarios que hayan aparcado aquí.</p>';
        }
    }

    window.abrirFormResena = function(idPlaza) {
        var form = document.getElementById('rs-form-'   + idPlaza);
        var btn  = document.querySelector('#rs-accion-' + idPlaza + ' .btn-escribir-resena');
        if (form) form.classList.add('open');
        if (btn)  btn.style.display = 'none';
    };

    window.cerrarFormResena = function(idPlaza) {
        var form = document.getElementById('rs-form-' + idPlaza);
        if (form) { form.classList.remove('open'); _resetForm(idPlaza); }
        var btn = document.querySelector('#rs-accion-' + idPlaza + ' .btn-escribir-resena');
        if (btn) btn.style.display = '';
    };

    window.enviarResena = function(idPlaza) {
        var picker    = document.getElementById('rs-picker-'  + idPlaza);
        var textarea  = document.getElementById('rs-comment-' + idPlaza);
        var msgEl     = document.getElementById('rs-msg-'     + idPlaza);
        var puntuacion = picker ? (picker._valor || 0) : 0;

        if (msgEl) { msgEl.textContent = ''; msgEl.className = 'r-msg'; }
        if (puntuacion < 1) {
            if (msgEl) { msgEl.textContent = 'Selecciona una puntuación.'; msgEl.className = 'r-msg error'; }
            return;
        }

        fetch('./reseñas/resenas_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin',
            body: JSON.stringify({
                id_plaza:   idPlaza,
                puntuacion: puntuacion,
                comentario: textarea ? textarea.value.trim() : ''
            })
        })
        .then(function(r) { return r.json().then(function(d) { return { ok: r.ok, data: d }; }); })
        .then(function(ref) {
            if (ref.ok && ref.data.success) {
                if (msgEl) { msgEl.textContent = '¡Reseña publicada!'; msgEl.className = 'r-msg ok'; }
                setTimeout(function() {
                    window.cerrarFormResena(idPlaza);
                    _cargarResenas(idPlaza);
                }, 1000);
            } else {
                var err = (ref.data.errors && Object.values(ref.data.errors)[0]) || ref.data.error || 'Error al publicar';
                if (msgEl) { msgEl.textContent = err; msgEl.className = 'r-msg error'; }
            }
        })
        .catch(function() {
            if (msgEl) { msgEl.textContent = 'Error de conexión.'; msgEl.className = 'r-msg error'; }
        });
    };

    function _initPicker(idPlaza) {
        var picker = document.getElementById('rs-picker-' + idPlaza);
        if (!picker) return;
        picker._valor = 0;
        var spans = picker.querySelectorAll('span');
        spans.forEach(function(span) {
            span.addEventListener('mouseover', function() {
                var v = parseInt(this.dataset.val);
                spans.forEach(function(s) { s.classList.toggle('hover', parseInt(s.dataset.val) <= v); });
            });
            span.addEventListener('mouseout', function() {
                var sel = picker._valor;
                spans.forEach(function(s) { s.classList.remove('hover'); s.classList.toggle('on', parseInt(s.dataset.val) <= sel); });
            });
            span.addEventListener('click', function() {
                picker._valor = parseInt(this.dataset.val);
                spans.forEach(function(s) { s.classList.remove('hover'); s.classList.toggle('on', parseInt(s.dataset.val) <= picker._valor); });
            });
        });
    }

    function _resetForm(idPlaza) {
        var picker = document.getElementById('rs-picker-'  + idPlaza);
        var area   = document.getElementById('rs-comment-' + idPlaza);
        var msg    = document.getElementById('rs-msg-'     + idPlaza);
        if (picker) { picker._valor = 0; picker.querySelectorAll('span').forEach(function(s){ s.classList.remove('on','hover'); }); }
        if (area)  area.value = '';
        if (msg)   { msg.textContent = ''; msg.className = 'r-msg'; }
    }

    function _stars(val, small) {
        var size = small ? '0.75rem' : '0.85rem';
        var html = '<span class="r-stars">';
        for (var i = 1; i <= 5; i++) {
            html += '<span class="r-star' + (i <= Math.round(val) ? ' on' : '') + '" style="font-size:' + size + '">&#9733;</span>';
        }
        return html + '</span>';
    }

    function _esc(str) {
        if (!str) return '';
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

})();