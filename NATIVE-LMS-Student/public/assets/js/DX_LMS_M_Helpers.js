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
    /**
     * Tunggu hingga SweetAlert2 tersedia
     * Ini mengatasi masalah race condition jika script dimuat dengan defer
     */
    function initDLS() {
        if (typeof Swal === 'undefined') {
            setTimeout(initDLS, 50);
            return;
        }

        function isDarkMode() {
            return document.documentElement.classList.contains('dark') || 
                   document.documentElement.getAttribute('data-theme') === 'dark';
        }

        // SweetAlert2 sudah ready, initialize DLS helpers
        window.DLS = {
            /**
             * Menampilkan dialog konfirmasi interaktif.
             * @param {Object} options
             * @param {string} [options.title]
             * @param {string} [options.text]
             * @param {string} [options.html]
             * @param {string} [options.confirmText]
             * @param {string} [options.cancelText]
             * @param {string} [options.icon] - 'question'|'warning'|'info'|'success'|'error'
             * @param {boolean} [options.danger] - jika true tombol konfirmasi berwarna merah
             * @returns {Promise<boolean>}
             */
            confirm: function(options) {
                options = options || {};
                var danger = Boolean(options.danger || options.isDestructive);
                var dark = isDarkMode();
                return Swal.fire({
                    icon: options.icon || (danger ? 'warning' : 'question'),
                    title: options.title || 'Konfirmasi Tindakan',
                    text: options.text || '',
                    html: options.html,
                    showCancelButton: true,
                    confirmButtonText: options.confirmText || 'Ya, Lanjutkan',
                    cancelButtonText: options.cancelText || 'Batal',
                    reverseButtons: true,
                    iconColor: danger ? '#ef4444' : '#d71920',
                    confirmButtonColor: danger ? '#dc2626' : '#d71920',
                    cancelButtonColor: dark ? '#334155' : '#64748b',
                    background: dark ? '#1e293b' : '#ffffff',
                    color: dark ? '#f8fafc' : '#0f172a',
                    customClass: {
                        popup: 'dls-swal-popup'
                    }
                }).then(function(result) {
                    return result.isConfirmed;
                });
            },

            /**
             * Menampilkan dialog sukses.
             * @param {string} title
             * @param {string} [text]
             */
            success: function(title, text) {
                var dark = isDarkMode();
                return Swal.fire({
                    icon: 'success',
                    title: title || 'Berhasil!',
                    text: text || '',
                    confirmButtonText: 'Selesai',
                    confirmButtonColor: '#10b981',
                    iconColor: '#10b981',
                    background: dark ? '#1e293b' : '#ffffff',
                    color: dark ? '#f8fafc' : '#0f172a',
                    customClass: {
                        popup: 'dls-swal-popup'
                    }
                });
            },

            /**
             * Menampilkan dialog error.
             * @param {string} title
             * @param {string} [text]
             */
            error: function(title, text) {
                var dark = isDarkMode();
                return Swal.fire({
                    icon: 'error',
                    title: title || 'Gagal!',
                    text: text || 'Terjadi kendala sistem.',
                    confirmButtonText: 'Mengerti',
                    confirmButtonColor: '#ef4444',
                    iconColor: '#ef4444',
                    background: dark ? '#1e293b' : '#ffffff',
                    color: dark ? '#f8fafc' : '#0f172a',
                    customClass: {
                        popup: 'dls-swal-popup'
                    }
                });
            },

            /**
             * Menampilkan dialog peringatan.
             * @param {string} title
             * @param {string} [text]
             */
            warning: function(title, text) {
                var dark = isDarkMode();
                return Swal.fire({
                    icon: 'warning',
                    title: title || 'Perhatian!',
                    text: text || '',
                    confirmButtonText: 'Mengerti',
                    confirmButtonColor: '#f59e0b',
                    iconColor: '#f59e0b',
                    background: dark ? '#1e293b' : '#ffffff',
                    color: dark ? '#f8fafc' : '#0f172a',
                    customClass: {
                        popup: 'dls-swal-popup'
                    }
                });
            },

            /**
             * Menampilkan dialog info.
             * @param {string} title
             * @param {string} [text]
             */
            info: function(title, text) {
                var dark = isDarkMode();
                return Swal.fire({
                    icon: 'info',
                    title: title || 'Informasi',
                    text: text || '',
                    confirmButtonText: 'Baik',
                    confirmButtonColor: '#2563eb',
                    iconColor: '#3b82f6',
                    background: dark ? '#1e293b' : '#ffffff',
                    color: dark ? '#f8fafc' : '#0f172a',
                    customClass: {
                        popup: 'dls-swal-popup'
                    }
                });
            },

            /**
             * Menampilkan dialog prompt input teks.
             * @param {Object} options
             * @returns {Promise<string|null>}
             */
            prompt: function(options) {
                options = options || {};
                var dark = isDarkMode();
                return Swal.fire({
                    icon: options.icon || 'question',
                    title: options.title || 'Masukkan Keterangan',
                    text: options.text || '',
                    input: options.inputType || 'textarea',
                    inputValue: options.inputValue || '',
                    inputPlaceholder: options.inputPlaceholder || 'Ketik di sini...',
                    showCancelButton: true,
                    confirmButtonText: options.confirmText || 'Kirim',
                    cancelButtonText: options.cancelText || 'Batal',
                    reverseButtons: true,
                    confirmButtonColor: options.danger ? '#dc2626' : '#d71920',
                    cancelButtonColor: dark ? '#334155' : '#64748b',
                    background: dark ? '#1e293b' : '#ffffff',
                    color: dark ? '#f8fafc' : '#0f172a',
                    customClass: {
                        popup: 'dls-swal-popup'
                    },
                    inputValidator: function(value) {
                        if (!value || !value.trim()) {
                            return 'Kolom ini tidak boleh kosong!';
                        }
                    }
                }).then(function(result) {
                    if (result.isConfirmed) {
                        return result.value ? result.value.trim() : '';
                    }
                    return null;
                });
            },

            /**
             * Menampilkan toast notification.
             * @param {string} title
             * @param {string} [icon]
             */
            toast: function(title, icon) {
                var dark = isDarkMode();
                return Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: icon || 'info',
                    title: title,
                    showConfirmButton: false,
                    timer: 3500,
                    timerProgressBar: true,
                    background: dark ? '#1e293b' : '#ffffff',
                    color: dark ? '#f8fafc' : '#0f172a'
                });
            },

            /**
             * Menampilkan modal loading processing.
             */
            loading: function(title, text) {
                var dark = isDarkMode();
                return Swal.fire({
                    title: title || 'Memproses...',
                    text: text || 'Mohon tunggu sebentar.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    background: dark ? '#1e293b' : '#ffffff',
                    color: dark ? '#f8fafc' : '#0f172a',
                    customClass: {
                        popup: 'dls-swal-popup'
                    },
                    didOpen: function() {
                        Swal.showLoading();
                    }
                });
            },

            close: function() {
                Swal.close();
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
