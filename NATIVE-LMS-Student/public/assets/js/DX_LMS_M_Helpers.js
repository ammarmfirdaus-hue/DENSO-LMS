/**
 * DLS Helper Functions untuk Learning Management System.
 *
 * File ini menyediakan wrapper functions untuk SweetAlert2 dengan styling
 * dan behavior yang konsisten sesuai design system DLS.
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
            confirm: function(options) {
                return Swal.fire({
                    icon: options.icon || 'question',
                    title: options.title || 'Konfirmasi',
                    text: options.text || '',
                    showCancelButton: true,
                    confirmButtonText: options.confirmText || 'Ya, Lanjutkan',
                    cancelButtonText: options.cancelText || 'Batal',
                    reverseButtons: true,
                    customClass: {
                        popup: 'dls-swal-popup'
                    }
                });
            },

            /**
             * Menampilkan dialog sukses.
             *
             * @param {string} title - Judul dialog
             * @param {string} text - Teks isi dialog (opsional)
             * @returns {Promise} Promise yang resolve setelah user menutup dialog
             */
            success: function(title, text) {
                return Swal.fire({
                    icon: 'success',
                    title: title || 'Berhasil!',
                    text: text || '',
                    confirmButtonText: 'OK'
                });
            },

            /**
             * Menampilkan dialog error.
             *
             * @param {string} title - Judul dialog
             * @param {string} text - Teks isi dialog (opsional)
             * @returns {Promise} Promise yang resolve setelah user menutup dialog
             */
            error: function(title, text) {
                return Swal.fire({
                    icon: 'error',
                    title: title || 'Gagal!',
                    text: text || '',
                    confirmButtonText: 'OK'
                });
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
