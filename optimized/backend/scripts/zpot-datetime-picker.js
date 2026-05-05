/**
 * ZpotDatePicker — Custom datetime picker al estilo Zpot
 * Uso: new ZpotDatePicker(inputEl, { min, onChange })
 */
(function (global) {
    'use strict';

    var MONTHS = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
    var DAYS   = ['L','M','X','J','V','S','D'];

    function pad(n) { return n < 10 ? '0' + n : '' + n; }

    function toLocalISO(d) {
        return d.getFullYear() + '-' + pad(d.getMonth()+1) + '-' + pad(d.getDate()) +
               'T' + pad(d.getHours()) + ':' + pad(d.getMinutes());
    }

    function ZpotDatePicker(input, opts) {
        opts = opts || {};
        this.input    = input;
        this.onChange = opts.onChange || null;
        this.minDate  = opts.min ? new Date(opts.min) : new Date();

        // Selected state
        this.selDate  = null; // Date object
        this.selHour  = 12;
        this.selMin   = 0;

        this._buildDropdown();
        this._bindInput();

        // Pre-fill if input already has value
        if (input.value) {
            var d = new Date(input.value);
            if (!isNaN(d)) {
                this.selDate = d;
                this.selHour = d.getHours();
                this.selMin  = d.getMinutes();
                this._updateInputDisplay();
            }
        }
    }

    ZpotDatePicker.prototype._buildDropdown = function () {
        var self = this;
        var wrap = document.createElement('div');
        wrap.className = 'zdp-wrap';
        wrap.innerHTML = [
            '<div class="zdp-dropdown" hidden>',
            '  <div class="zdp-cal">',
            '    <div class="zdp-cal-header">',
            '      <button type="button" class="zdp-nav" data-dir="-1">&#8592;</button>',
            '      <span class="zdp-month-label"></span>',
            '      <button type="button" class="zdp-nav" data-dir="1">&#8594;</button>',
            '    </div>',
            '    <div class="zdp-days-row"></div>',
            '    <div class="zdp-grid"></div>',
            '  </div>',
            '  <div class="zdp-time">',
            '    <div class="zdp-time-label">Hora</div>',
            '    <div class="zdp-time-row">',
            '      <div class="zdp-spinner" data-type="hour">',
            '        <button type="button" class="zdp-spin-btn" data-dir="1">&#8593;</button>',
            '        <span class="zdp-spin-val"></span>',
            '        <button type="button" class="zdp-spin-btn" data-dir="-1">&#8595;</button>',
            '      </div>',
            '      <span class="zdp-time-sep">:</span>',
            '      <div class="zdp-spinner" data-type="min">',
            '        <button type="button" class="zdp-spin-btn" data-dir="1">&#8593;</button>',
            '        <span class="zdp-spin-val"></span>',
            '        <button type="button" class="zdp-spin-btn" data-dir="-1">&#8595;</button>',
            '      </div>',
            '    </div>',
            '  </div>',
            '  <div class="zdp-footer">',
            '    <button type="button" class="zdp-btn-clear">Limpiar</button>',
            '    <button type="button" class="zdp-btn-ok">Confirmar</button>',
            '  </div>',
            '</div>'
        ].join('');

        // Insert wrapper after input
        this.input.parentNode.insertBefore(wrap, this.input.nextSibling);
        wrap.insertBefore(this.input, wrap.firstChild);

        this.wrap     = wrap;
        this.dropdown = wrap.querySelector('.zdp-dropdown');
        this.grid     = wrap.querySelector('.zdp-grid');
        this.label    = wrap.querySelector('.zdp-month-label');

        // Init view to current month
        var now = new Date();
        this.viewYear  = now.getFullYear();
        this.viewMonth = now.getMonth();

        // Days header
        var daysRow = wrap.querySelector('.zdp-days-row');
        DAYS.forEach(function (d) {
            var s = document.createElement('span');
            s.textContent = d;
            daysRow.appendChild(s);
        });

        // Nav arrows
        wrap.querySelectorAll('.zdp-nav').forEach(function (btn) {
            btn.addEventListener('click', function () {
                self.viewMonth += parseInt(btn.dataset.dir, 10);
                if (self.viewMonth > 11) { self.viewMonth = 0;  self.viewYear++; }
                if (self.viewMonth < 0)  { self.viewMonth = 11; self.viewYear--; }
                self._renderCalendar();
            });
        });

        // Time spinners
        wrap.querySelectorAll('.zdp-spinner').forEach(function (spin) {
            spin.querySelectorAll('.zdp-spin-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var dir  = parseInt(btn.dataset.dir, 10);
                    var type = spin.dataset.type;
                    if (type === 'hour') {
                        self.selHour = (self.selHour + dir + 24) % 24;
                    } else {
                        self.selMin = (self.selMin + dir * 15 + 60) % 60;
                    }
                    self._renderTime();
                });
            });
        });

        wrap.querySelector('.zdp-btn-ok').addEventListener('click', function () {
            if (!self.selDate) return;
            var d = new Date(self.selDate);
            d.setHours(self.selHour, self.selMin, 0, 0);
            self.input.value = toLocalISO(d);
            self._updateInputDisplay();
            self.dropdown.hidden = true;
            if (self.onChange) self.onChange(d);
        });

        wrap.querySelector('.zdp-btn-clear').addEventListener('click', function () {
            self.selDate = null;
            self.input.value = '';
            self._updateDisplayEl('');
            self.dropdown.hidden = true;
            if (self.onChange) self.onChange(null);
        });

        // Close on outside click
        document.addEventListener('click', function (e) {
            if (!wrap.contains(e.target)) self.dropdown.hidden = true;
        });

        this._renderCalendar();
        this._renderTime();
    };

    ZpotDatePicker.prototype._bindInput = function () {
        var self = this;
        // Replace native input with a styled display button
        var display = document.createElement('button');
        display.type = 'button';
        display.className = 'zdp-display';
        display.innerHTML = '<span class="zdp-display-text">Seleccionar fecha y hora</span><span class="zdp-display-icon">&#128197;</span>';
        this.input.style.display = 'none';
        this.input.parentNode.insertBefore(display, this.input);
        this.displayEl = display;

        display.addEventListener('click', function (e) {
            e.stopPropagation();
            self.dropdown.hidden = !self.dropdown.hidden;
            if (!self.dropdown.hidden) self._renderCalendar();
        });
    };

    ZpotDatePicker.prototype._updateDisplayEl = function (text) {
        if (this.displayEl) {
            this.displayEl.querySelector('.zdp-display-text').textContent = text || 'Seleccionar fecha y hora';
        }
    };

    ZpotDatePicker.prototype._updateInputDisplay = function () {
        if (!this.selDate) return;
        var d = new Date(this.selDate);
        d.setHours(this.selHour, this.selMin, 0, 0);
        var label = pad(d.getDate()) + '/' + pad(d.getMonth()+1) + '/' + d.getFullYear() +
                    ' — ' + pad(this.selHour) + ':' + pad(this.selMin);
        this._updateDisplayEl(label);
    };

    ZpotDatePicker.prototype._renderCalendar = function () {
        var self    = this;
        var y       = this.viewYear;
        var m       = this.viewMonth;
        this.label.textContent = MONTHS[m] + ' ' + y;
        this.grid.innerHTML = '';

        var first = new Date(y, m, 1).getDay(); // 0=sun
        // Convert sunday=0 to monday=0
        first = (first === 0) ? 6 : first - 1;

        var days = new Date(y, m + 1, 0).getDate();
        var today = new Date(); today.setHours(0,0,0,0);

        // Empty cells before 1st
        for (var i = 0; i < first; i++) {
            var empty = document.createElement('span');
            this.grid.appendChild(empty);
        }

        for (var d = 1; d <= days; d++) {
            (function (day) {
                var cell = document.createElement('button');
                cell.type = 'button';
                cell.textContent = day;
                cell.className = 'zdp-day';

                var cellDate = new Date(y, m, day);
                cellDate.setHours(0,0,0,0);

                if (cellDate < today && cellDate.getTime() !== today.getTime()) {
                    cell.disabled = true;
                    cell.classList.add('zdp-day--past');
                }

                if (self.selDate) {
                    var sel = new Date(self.selDate); sel.setHours(0,0,0,0);
                    if (cellDate.getTime() === sel.getTime()) cell.classList.add('zdp-day--selected');
                }

                if (cellDate.getTime() === today.getTime()) cell.classList.add('zdp-day--today');

                cell.addEventListener('click', function () {
                    self.selDate = new Date(y, m, day);
                    self._renderCalendar();
                    self._updateInputDisplay();
                });

                self.grid.appendChild(cell);
            })(d);
        }
    };

    ZpotDatePicker.prototype._renderTime = function () {
        var spinners = this.wrap.querySelectorAll('.zdp-spinner');
        spinners[0].querySelector('.zdp-spin-val').textContent = pad(this.selHour);
        spinners[1].querySelector('.zdp-spin-val').textContent = pad(this.selMin);
    };

    global.ZpotDatePicker = ZpotDatePicker;

    // Datepickers Zpot
    var pickerEntrada = new ZpotDatePicker(inputEntrada, {
        min: ahora.toISOString().slice(0, 16),
        onChange: function (d) {
            if (!d) return;
            // Ajusta el mínimo de salida a entrada + 1h
            var minSal = new Date(d);
            minSal.setHours(minSal.getHours() + 1);
            pickerSalida.minDate = minSal;
            actualizarPrecio();
        }
    });
    var pickerSalida = new ZpotDatePicker(inputSalida, {
        min: ahora.toISOString().slice(0, 16),
        onChange: function () { actualizarPrecio(); }
    });
})(window);
