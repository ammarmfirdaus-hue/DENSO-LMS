import Swal from 'sweetalert2';

/**
 * Konfigurasi helper SweetAlert2 resmi untuk DENSO Learning Management System.
 * Menyediakan tampilan modal popup profesional, detail, dan responsive.
 */
const dlsSwal = Swal.mixin({
  customClass: {
    popup: 'rounded-3xl border border-outline/40 bg-white p-6 shadow-2xl font-sans max-w-md w-full',
    title: 'text-lg sm:text-xl font-bold text-on-surface',
    htmlContainer: 'text-xs sm:text-sm text-secondary leading-relaxed mt-2',
    confirmButton: 'inline-flex items-center justify-center rounded-xl bg-primary px-5 py-2.5 text-xs sm:text-sm font-bold text-white shadow-sm hover:brightness-110 active:scale-95 transition cursor-pointer mx-1.5',
    cancelButton: 'inline-flex items-center justify-center rounded-xl border border-outline/70 bg-white px-5 py-2.5 text-xs sm:text-sm font-semibold text-secondary hover:bg-surface-low active:scale-95 transition cursor-pointer mx-1.5',
    denyButton: 'inline-flex items-center justify-center rounded-xl bg-red-600 px-5 py-2.5 text-xs sm:text-sm font-bold text-white shadow-sm hover:bg-red-700 active:scale-95 transition cursor-pointer mx-1.5',
  },
  buttonsStyling: false,
});

export const showAlert = {
  /**
   * Dialog Sukses dengan ikon dan pesan yang jelas.
   * @param {string} title
   * @param {string} [text]
   */
  success: (title, text = '') => {
    return dlsSwal.fire({
      icon: 'success',
      title: title || 'Berhasil!',
      text,
      confirmButtonText: 'Selesai',
      iconColor: '#10b981',
    });
  },

  /**
   * Dialog Error dengan ikon dan pesan masalah yang informatif.
   * @param {string} title
   * @param {string|Error} [text]
   */
  error: (title, text = '') => {
    const message = typeof text === 'string' ? text : text?.message || 'Terjadi kesalahan sistem.';
    return dlsSwal.fire({
      icon: 'error',
      title: title || 'Gagal!',
      text: message,
      confirmButtonText: 'Mengerti',
      iconColor: '#ef4444',
    });
  },

  /**
   * Dialog Peringatan (Warning).
   * @param {string} title
   * @param {string} [text]
   */
  warning: (title, text = '') => {
    return dlsSwal.fire({
      icon: 'warning',
      title: title || 'Perhatian!',
      text,
      confirmButtonText: 'Mengerti',
      iconColor: '#f59e0b',
    });
  },

  /**
   * Dialog Info.
   * @param {string} title
   * @param {string} [text]
   */
  info: (title, text = '') => {
    return dlsSwal.fire({
      icon: 'info',
      title: title || 'Informasi',
      text,
      confirmButtonText: 'Baik',
      iconColor: '#3b82f6',
    });
  },

  /**
   * Dialog Konfirmasi dengan SweetAlert2 (misal Hapus / Kirim / Publikasi).
   * @param {{
   *   title?: string,
   *   text?: string,
   *   confirmText?: string,
   *   cancelText?: string,
   *   icon?: 'warning'|'question'|'info'|'success'|'error',
   *   danger?: boolean
   * }} options
   * @returns {Promise<boolean>}
   */
  confirm: async ({
    title = 'Konfirmasi Tindakan',
    text = 'Apakah Anda yakin ingin melanjutkan?',
    confirmText = 'Ya, Lanjutkan',
    cancelText = 'Batal',
    icon = 'warning',
    danger = false,
  } = {}) => {
    const result = await dlsSwal.fire({
      icon,
      title,
      text,
      showCancelButton: true,
      confirmButtonText: confirmText,
      cancelButtonText: cancelText,
      reverseButtons: true,
      iconColor: danger ? '#dc2626' : '#d71920',
      customClass: {
        popup: 'rounded-3xl border border-outline/40 bg-white p-6 shadow-2xl font-sans max-w-md w-full',
        title: `text-lg sm:text-xl font-bold ${danger ? 'text-red-700' : 'text-on-surface'}`,
        htmlContainer: 'text-xs sm:text-sm text-secondary leading-relaxed mt-2',
        confirmButton: danger
          ? 'inline-flex items-center justify-center rounded-xl bg-red-600 px-5 py-2.5 text-xs sm:text-sm font-bold text-white shadow-sm hover:bg-red-700 active:scale-95 transition cursor-pointer mx-1.5'
          : 'inline-flex items-center justify-center rounded-xl bg-primary px-5 py-2.5 text-xs sm:text-sm font-bold text-white shadow-sm hover:brightness-110 active:scale-95 transition cursor-pointer mx-1.5',
        cancelButton: 'inline-flex items-center justify-center rounded-xl border border-outline/70 bg-white px-5 py-2.5 text-xs sm:text-sm font-semibold text-secondary hover:bg-surface-low active:scale-95 transition cursor-pointer mx-1.5',
      },
    });
    return result.isConfirmed;
  },

  /**
   * Dialog Input / Prompt (misal alasan penolakan, catatan evaluasi).
   * @param {{
   *   title?: string,
   *   text?: string,
   *   inputPlaceholder?: string,
   *   inputValue?: string,
   *   confirmText?: string,
   *   cancelText?: string,
   *   danger?: boolean,
   *   inputType?: 'text'|'textarea'
   * }} options
   * @returns {Promise<string|null>} null jika dibatalkan, string jika dikonfirmasi
   */
  prompt: async ({
    title = 'Masukkan Keterangan',
    text = '',
    inputPlaceholder = 'Ketik di sini...',
    inputValue = '',
    confirmText = 'Kirim',
    cancelText = 'Batal',
    danger = false,
    inputType = 'textarea',
  } = {}) => {
    const result = await dlsSwal.fire({
      icon: danger ? 'warning' : 'info',
      title,
      text,
      input: inputType,
      inputValue,
      inputPlaceholder,
      showCancelButton: true,
      confirmButtonText: confirmText,
      cancelButtonText: cancelText,
      reverseButtons: true,
      iconColor: danger ? '#dc2626' : '#d71920',
      inputValidator: (value) => {
        if (!value || !value.trim()) {
          return 'Kolom ini wajib diisi!';
        }
      },
      customClass: {
        popup: 'rounded-3xl border border-outline/40 bg-white p-6 shadow-2xl font-sans max-w-lg w-full',
        title: `text-lg sm:text-xl font-bold ${danger ? 'text-red-700' : 'text-on-surface'}`,
        htmlContainer: 'text-xs sm:text-sm text-secondary leading-relaxed mt-2',
        input: 'rounded-xl border border-outline p-3 text-sm font-sans w-full outline-none focus:border-primary mt-3',
        confirmButton: danger
          ? 'inline-flex items-center justify-center rounded-xl bg-red-600 px-5 py-2.5 text-xs sm:text-sm font-bold text-white shadow-sm hover:bg-red-700 active:scale-95 transition cursor-pointer mx-1.5'
          : 'inline-flex items-center justify-center rounded-xl bg-primary px-5 py-2.5 text-xs sm:text-sm font-bold text-white shadow-sm hover:brightness-110 active:scale-95 transition cursor-pointer mx-1.5',
        cancelButton: 'inline-flex items-center justify-center rounded-xl border border-outline/70 bg-white px-5 py-2.5 text-xs sm:text-sm font-semibold text-secondary hover:bg-surface-low active:scale-95 transition cursor-pointer mx-1.5',
      },
    });
    if (result.isConfirmed) {
      return result.value?.trim() || '';
    }
    return null;
  },

  /**
   * Toast notification di pojok kanan atas.
   * @param {string} title
   * @param {'success'|'error'|'info'|'warning'} [icon]
   */
  toast: (title, icon = 'success') => {
    return Swal.fire({
      toast: true,
      position: 'top-end',
      icon,
      title,
      showConfirmButton: false,
      timer: 3500,
      timerProgressBar: true,
      iconColor: icon === 'success' ? '#10b981' : icon === 'error' ? '#ef4444' : '#3b82f6',
      customClass: {
        popup: 'rounded-2xl border border-outline/50 bg-white shadow-xl p-3.5 text-xs sm:text-sm font-sans',
      },
    });
  },

  /**
   * Loading popup saat proses sedang dikirim ke server.
   * @param {string} [title]
   * @param {string} [text]
   */
  loading: (title = 'Memproses...', text = 'Mohon tunggu sebentar.') => {
    dlsSwal.fire({
      title,
      text,
      allowOutsideClick: false,
      allowEscapeKey: false,
      showConfirmButton: false,
      didOpen: () => {
        Swal.showLoading();
      },
    });
  },

  close: () => {
    Swal.close();
  },
};

export default showAlert;
