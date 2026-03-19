(function () {
    var MALAGA = [36.7213, -4.4214];
    var DEFAULT_ZOOM = 14;

    var map = null;
    var markersLayer = null;
    var plazas = [];

    var detailPanel = document.getElementById('detailPanel');
    var detailClose = document.getElementById('detailClose');
    var detailPhoto = document.getElementById('detailPhoto');
    var detailPhotoPlaceholder = document.getElementById('detailPhotoPlaceholder');
    var detailTitle = document.getElementById('detailTitle');
    var detailPrice = document.getElementById('detailPrice');
    var detailOwner = document.getElementById('detailOwner');
    var detailLocation = document.getElementById('detailLocation');
    var detailDescription = document.getElementById('detailDescription');
    var detailSize = document.getElementById('detailSize');
    var detailBook = document.getElementById('detailBook');

    var accountBtn = document.getElementById('accountBtn');
    var accountDropdown = document.getElementById('accountDropdown');
    var filtersBtn = document.getElementById('filtersBtn');
    var filtersDropdown = document.getElementById('filtersDropdown');

    function apiUrl(path) {
        var base = window.location.pathname.replace(/\/[^/]*$/, '');
        return base + path;
    }

    function checkAuth() {
        return fetch(apiUrl('/sesion/me_api.php'), { credentials: 'same-origin' })
            .then(function (r) {
                if (r.status === 401) {
                    window.location.href = apiUrl('/sesion/login.php');
                    return Promise.reject(new Error('Not authenticated'));
                }
                return r.json();
            });
    }

    function loadPlazas() {
        return fetch(apiUrl('/plazas_api.php'), { credentials: 'same-origin' })
            .then(function (r) {
                if (r.status === 401) {
                    window.location.href = apiUrl('/sesion/login.php');
                    return Promise.reject(new Error('Not authenticated'));
                }
                return r.json();
            })
            .then(function (data) {
                plazas = data.plazas || [];
                return plazas;
            });
    }

    function getMarkerPosition(plaza, index) {
        if (plaza.lat != null && plaza.lng != null) {
            return [plaza.lat, plaza.lng];
        }
        var knownMalaga = [
            [36.7213, -4.4214],
            [36.7198, -4.4232],
            [36.7235, -4.4198],
            [36.7182, -4.4190],
            [36.7248, -4.4180],
            [36.7260, -4.4210],
            [36.7205, -4.4250],
            [36.7190, -4.4218]
        ];
        if (index < knownMalaga.length) {
            return knownMalaga[index];
        }
        return [
            MALAGA[0] + (index * 0.002),
            MALAGA[1] + (index * 0.0015)
        ];
    }

    function createParkingIcon() {
        var html = '<div style="width:40px;height:40px;background:#c9a227;color:#111;border:2px solid #111;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:16px;font-family:sans-serif;box-shadow:0 2px 10px rgba(0,0,0,0.3);">P</div>';
        return L.divIcon({
            className: 'zpot-parking-marker',
            html: html,
            iconSize: [40, 40],
            iconAnchor: [20, 20]
        });
    }

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
    }

    function addMarkers() {
        if (!markersLayer || !plazas.length) return;
        markersLayer.clearLayers();
        var bounds = [];
        plazas.forEach(function (p, i) {
            var pos = getMarkerPosition(p, i);
            bounds.push(pos);
            var marker = L.marker(pos, { icon: createParkingIcon() });
            marker.on('click', function () { openDetail(p); });
            marker.bindTooltip(p.direccion || 'Plaza', {
                direction: 'top',
                offset: [0, -20],
                opacity: 0.9
            });
            markersLayer.addLayer(marker);
        });
        if (bounds.length > 0) {
            map.fitBounds(bounds, { padding: [40, 40], maxZoom: 15 });
        }
    }

    function openDetail(plaza) {
        detailTitle.textContent = plaza.direccion || 'Plaza #' + plaza.id;
        detailPrice.textContent = 'Precio: ' + ((plaza.precio != null && plaza.precio > 0)
            ? plaza.precio.toFixed(2) + ' €/h'
            : 'no especificado');
        detailPrice.hidden = false;
        detailOwner.textContent = 'Anfitrión: ' + (plaza.owner || '—');
        detailLocation.textContent = plaza.direccion || '—';
        detailDescription.textContent = plaza.descripcion || 'Sin descripción.';
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
        detailBook.href = apiUrl('/reserva.php?id_plaza=' + plaza.id);
        detailBook.textContent = 'Reservar';
        detailBook.dataset.plazaId = plaza.id;
        detailPanel.hidden = false;
    }

    function closeDetail() {
        detailPanel.hidden = true;
    }

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

    function toggleDropdown(dropdown, btn, open) {
        var isOpen = open !== undefined ? open : dropdown.hidden;
        dropdown.hidden = !isOpen;
        if (btn) btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
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
        document.querySelectorAll('.filters-dropdown input[name="filter"]').forEach(function (cb) { cb.checked = false; });
    });
    document.getElementById('applyFilters').addEventListener('click', function () {
        toggleDropdown(filtersDropdown, filtersBtn, false);
    });

    (function showPlazaCreatedBannerIfNeeded() {
        var params = new URLSearchParams(window.location.search);
        if (params.get('plaza_created') !== '1') return;
        var banner = document.getElementById('plazaCreatedBanner');
        if (!banner) return;
        banner.hidden = false;
        var cleanUrl = window.location.pathname + (window.location.hash || '');
        if (window.history.replaceState) {
            window.history.replaceState(null, '', cleanUrl);
        }
        setTimeout(function () {
            banner.hidden = true;
        }, 5000);
    })();

    checkAuth()
        .then(function () {
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
        .catch(function () {});
})();
