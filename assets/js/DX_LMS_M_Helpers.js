/**
 * DLS Helper Functions untuk Learning Management System.
 *
 * File ini menyediakan wrapper functions untuk SweetAlert2 dengan styling
 * dan behavior yang konsisten sesuai design system LMS.
 *
 * Dependencies: SweetAlert2 (harus dimuat terlebih dahulu)
 *
 * @author DLS Development Team
 * @version 1.0.0
 */

(function() {
    'use strict';

    /**
     * Tunggu hingga SweetAlert2 tersedia
     * Ini mengatasi masalah race condition jika script dimuat dengan defer
     */
    function initDLS() {
        if (typeof Swal === 'undefined') {
            // Retry setelah 50ms jika Swal belum loaded
            setTimeout(initDLS, 50);
            return;
        }

        // SweetAlert2 sudah ready, initialize DLS helpers
        window.DLS = {
            /**
             * Menampilkan dialog konfirmasi.
             *
             * @param {Object} options - Opsi konfigurasi dialog
             * @param {string} options.icon - Icon dialog ('question', 'warning', etc)
             * @param {string} options.title - Judul dialog
             * @param {string} options.text - Teks isi dialog
             * @param {string} options.confirmText - Teks tombol konfirmasi
             * @param {string} options.cancelText - Teks tombol batal
             * @returns {Promise} Promise yang resolve dengan hasil dialog
             */
            confirm: function(optionsOrTitle, textOrHtml, confirmText, cancelText, icon) {
                let opts = {};
                if (typeof optionsOrTitle === 'object' && optionsOrTitle !== null) {
                    opts = optionsOrTitle;
                } else {
                    opts = {
                        title: optionsOrTitle || 'Konfirmasi',
                        text: textOrHtml || '',
                        confirmText: confirmText || 'Ya, Lanjutkan',
                        cancelText: cancelText || 'Batal',
                        icon: icon || 'question'
                    };
                }

                const content = opts.text || opts.html || '';
                const isHtml = /<[a-z][\s\S]*>/i.test(content);

                const swalConfig = {
                    icon: opts.icon || 'question',
                    title: opts.title || 'Konfirmasi',
                    showCancelButton: true,
                    confirmButtonText: opts.confirmText || 'Ya, Lanjutkan',
                    cancelButtonText: opts.cancelText || 'Batal',
                    reverseButtons: true,
                    customClass: {
                        popup: 'dls-swal-popup'
                    }
                };

                if (isHtml || opts.html) {
                    swalConfig.html = content;
                } else {
                    swalConfig.text = content;
                }

                return Swal.fire(swalConfig);
            },

            /**
             * Menampilkan dialog sukses.
             *
             * @param {string} title - Judul dialog
             * @param {string} text - Teks isi dialog (opsional, mendukung HTML)
             * @returns {Promise} Promise yang resolve setelah user menutup dialog
             */
            success: function(title, text) {
                const isHtml = text && /<[a-z][\s\S]*>/i.test(text);
                const config = {
                    icon: 'success',
                    title: title || 'Berhasil!',
                    confirmButtonText: 'OK'
                };
                if (isHtml) {
                    config.html = text;
                } else {
                    config.text = text || '';
                }
                return Swal.fire(config);
            },

            /**
             * Menampilkan dialog error.
             *
             * @param {string} title - Judul dialog
             * @param {string} text - Teks isi dialog (opsional, mendukung HTML)
             * @returns {Promise} Promise yang resolve setelah user menutup dialog
             */
            error: function(title, text) {
                const isHtml = text && /<[a-z][\s\S]*>/i.test(text);
                const config = {
                    icon: 'error',
                    title: title || 'Gagal!',
                    confirmButtonText: 'OK'
                };
                if (isHtml) {
                    config.html = text;
                } else {
                    config.text = text || '';
                }
                return Swal.fire(config);
            },

            /**
             * Menampilkan toast notification.
             *
             * @param {string} title - Teks toast
             * @param {string} icon - Icon toast ('success', 'error', 'info', 'warning')
             * @returns {Promise} Promise yang resolve setelah toast ditutup
             */
            toast: function(title, icon) {
                return Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: icon || 'info',
                    title: title,
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
            }
        };

        // Dispatch custom event untuk memberitahu bahwa DLS helpers sudah ready
        if (typeof CustomEvent !== 'undefined') {
            window.dispatchEvent(new CustomEvent('DLSReady'));
        }
    }

    // Start initialization
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDLS);
    } else {
        initDLS();
    }
})();
