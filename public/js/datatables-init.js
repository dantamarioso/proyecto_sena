/*
 * DataTables Init
 * - Aplica DataTables a tablas con la clase .js-datatable
 * - Usa scrollY/scrollX para tener barra deslizadora persistente
 * - Integra algunos filtros externos cuando existen (Usuarios)
 */

(function () {
    'use strict';

    const DT_DEFAULT_SCROLL_Y = '60vh';
    const dtInstances = [];
    let revealTriggered = false;

    function dtReady() {
        return !!(window.jQuery && window.jQuery.fn && window.jQuery.fn.DataTable);
    }

    function debounce(fn, wait) {
        let t = null;
        return function () {
            const ctx = this;
            const args = arguments;
            clearTimeout(t);
            t = setTimeout(function () {
                fn.apply(ctx, args);
            }, wait);
        };
    }

    function languageEs() {
        return {
            processing: 'Procesando...',
            search: 'Buscar:',
            lengthMenu: 'Mostrar _MENU_ registros',
            info: 'Mostrando _START_ a _END_ de _TOTAL_ registro(s)',
            infoEmpty: 'Mostrando 0 a 0 de 0 registro(s)',
            infoFiltered: '(filtrado de _MAX_ total)',
            loadingRecords: 'Cargando...',
            zeroRecords: 'No se encontraron resultados',
            emptyTable: 'No hay datos disponibles',
            paginate: {
                first: 'Primero',
                previous: 'Anterior',
                next: 'Siguiente',
                last: 'Último'
            },
            aria: {
                sortAscending: ': activar para ordenar ascendente',
                sortDescending: ': activar para ordenar descendente'
            }
        };
    }

    function initDataTableFor(tableEl, onInitComplete) {
        const $ = window.jQuery;
        if (!tableEl || !dtReady()) return null;
        if ($.fn.DataTable.isDataTable(tableEl)) return $(tableEl).DataTable();

        const $table = $(tableEl);
        const scrollY = $table.data('dtScrollY') || DT_DEFAULT_SCROLL_Y;
        const externalSearch = String($table.data('dtExternalSearch') || '') === '1';
        const pageLength = parseInt(String($table.data('dtPageLength') || '20'), 10) || 20;

        const dom = externalSearch
            ? "rt<'d-flex justify-content-between align-items-center pt-2'<'dt-info'i><'dt-paging'p>>"
            : "<'row mb-2'<'col-sm-12 col-md-6'f><'col-sm-12 col-md-6 text-end'>>rt<'row mt-2'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>";

        const dt = $table.DataTable({
            scrollX: true,
            scrollY: scrollY,
            scrollCollapse: true,
            paging: true,
            pageLength: pageLength,
            lengthChange: false,
            pagingType: 'simple_numbers',
            searching: true,
            info: true,
            order: [],
            autoWidth: false,
            deferRender: true,
            dom: dom,
            language: languageEs(),
            columnDefs: [
                { targets: 'no-sort', orderable: false }
            ],
            initComplete: function () {
                if (typeof onInitComplete === 'function') {
                    try {
                        onInitComplete(this.api());
                    } catch (e) {
                        // no-op
                    }
                }
            }
        });

        // Evitar doble scroll (Bootstrap .table-responsive) cuando DataTables ya maneja scroll
        const $responsive = $table.closest('.table-responsive');
        if ($responsive.length) {
            $responsive.addClass('datatable-enhanced');
            $responsive.css('overflow', 'visible');
        }

        dtInstances.push(dt);
        return dt;
    }

    function revealAfterLayout() {
        if (revealTriggered) return;
        revealTriggered = true;

        const reveal = function () {
            try {
                dtInstances.forEach(function (dt) {
                    try {
                        dt.columns.adjust();
                    } catch (e) {
                        // no-op
                    }
                });
            } finally {
                document.documentElement.classList.add('dt-ready');
                document.documentElement.classList.remove('dt-enhancing');
            }
        };

        if (typeof window.requestAnimationFrame === 'function') {
            window.requestAnimationFrame(function () {
                window.requestAnimationFrame(reveal);
            });
        } else {
            setTimeout(reveal, 0);
        }
    }

    // ================================
    // Usuarios: filtros externos
    // ================================
    let usuariosFilterRegistered = false;

    function registerUsuariosExternalFilter(tableId) {
        if (usuariosFilterRegistered) return;
        const $ = window.jQuery;
        if (!dtReady()) return;

        $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
            if (!settings || !settings.nTable || settings.nTable.id !== tableId) {
                return true;
            }

            const tr = settings.aoData && settings.aoData[dataIndex] ? settings.aoData[dataIndex].nTr : null;
            if (!tr) return true;

            const filtroRol = document.getElementById('filtro-rol') ? (document.getElementById('filtro-rol').value || '') : '';
            const filtroEstado = document.getElementById('filtro-estado') ? (document.getElementById('filtro-estado').value || '') : '';

            const rol = (tr.getAttribute('data-rol') || '').toLowerCase();
            const estado = String(tr.getAttribute('data-estado') || '');

            if (filtroRol && rol !== String(filtroRol).toLowerCase()) {
                return false;
            }

            if (filtroEstado !== '' && estado !== String(filtroEstado)) {
                return false;
            }

            return true;
        });

        usuariosFilterRegistered = true;
    }

    function wireUsuariosTable(dt, tableEl) {
        if (!dt || !tableEl) return;
        if (tableEl.getAttribute('data-dt-wired') === '1') return;
        tableEl.setAttribute('data-dt-wired', '1');

        registerUsuariosExternalFilter(tableEl.id);

        const inputBusqueda = document.getElementById('busqueda');
        const filtroRol = document.getElementById('filtro-rol');
        const filtroEstado = document.getElementById('filtro-estado');
        const btnLimpiar = document.getElementById('btn-limpiar');

        if (inputBusqueda) {
            inputBusqueda.addEventListener('keyup', function () {
                dt.search(this.value || '');
                dt.draw();
            });
        }

        [filtroRol, filtroEstado].forEach(function (el) {
            if (!el) return;
            el.addEventListener('change', function () {
                dt.draw();
            });
        });

        if (btnLimpiar) {
            btnLimpiar.addEventListener('click', function (e) {
                e.preventDefault();
                if (inputBusqueda) inputBusqueda.value = '';
                if (filtroRol) filtroRol.value = '';
                if (filtroEstado) filtroEstado.value = '';
                dt.search('');
                dt.draw();
            });
        }
    }

    function initAllTables() {
        if (!dtReady()) return;

        const tables = document.querySelectorAll('table.js-datatable');
        let initializedAny = false;
        let pending = 0;
        const doneOne = function () {
            pending = Math.max(0, pending - 1);
            if (initializedAny && pending === 0) {
                revealAfterLayout();
            }
        };

        tables.forEach(function (tableEl) {
            const $ = window.jQuery;
            if ($ && $.fn && $.fn.DataTable && $.fn.DataTable.isDataTable && $.fn.DataTable.isDataTable(tableEl)) {
                initializedAny = true;
                const dt = $(tableEl).DataTable();
                dtInstances.push(dt);
                if (tableEl.id === 'tabla-usuarios') {
                    wireUsuariosTable(dt, tableEl);
                }
                return;
            }

            pending++;
            const dt = initDataTableFor(tableEl, function () {
                doneOne();
            });
            if (dt) {
                initializedAny = true;
                if (tableEl.id === 'tabla-usuarios') {
                    wireUsuariosTable(dt, tableEl);
                }
            } else {
                doneOne();
            }
        });

        if (initializedAny && pending === 0) {
            revealAfterLayout();
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        initAllTables();

        // Si no hay tablas .js-datatable en la vista, no bloquear nada
        if (!document.querySelector('table.js-datatable')) {
            document.documentElement.classList.remove('dt-enhancing');
        }
    });

    // Fallback: si DataTables no carga, mostrar tabla normal
    window.setTimeout(function () {
        if (document.documentElement.classList.contains('dt-enhancing') && !dtReady()) {
            document.documentElement.classList.remove('dt-enhancing');
        }
    }, 6000);

    window.addEventListener('resize', debounce(function () {
        dtInstances.forEach(function (dt) {
            try {
                dt.columns.adjust();
            } catch (e) {
                // no-op
            }
        });
    }, 150));
})();
