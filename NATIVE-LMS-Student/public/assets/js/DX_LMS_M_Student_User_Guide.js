/**
 * DLS Student Interactive Tour & User Guide (Driver.js)
 * Panduan interaktif per-bagian (section-by-section) dan role-aware
 * untuk Portal Pembelajaran Siswa DENSO-LMS.
 */
(function() {
    'use strict';

    var PAGE_META = {
        'beranda': {
            title: 'Beranda Siswa',
            summary: 'Pantau ringkasan progres belajar Anda, cari kursus aktif, lalu tekan Lanjutkan Belajar untuk langsung masuk ke ruang materi.'
        },
        'courses': {
            title: 'Katalog Course',
            summary: 'Jelajahi seluruh pelatihan Wajib & Pilihan, saring kategori, buka materi belajar, atau ajukan permintaan akses (enrollment).'
        },
        'course-detail': {
            title: 'Ruang Belajar & Detail Course',
            summary: 'Pelajari materi tiap sesi secara berurutan hingga 100%, kerjakan evaluasi Quiz, berdiskusi di Forum, dan pantau Capaian Anda.'
        },
        'course-access': {
            title: 'Permintaan Akses Course',
            summary: 'Periksa status persetujuan akses kursus Anda dan kirim pengajuan enrollment ke Admin.'
        },
        'filters': {
            title: 'Pencarian & Filter Course',
            summary: 'Cari kursus berdasarkan kata kunci, reset filter, dan urutkan daftar pelatihan.'
        },
        'nilai': {
            title: 'Buku Nilai Saya',
            summary: 'Lihat rekapitulasi progres materi (%) dan nilai evaluasi Quiz pada seluruh kursus yang Anda ikuti.'
        },
        'riwayat': {
            title: 'Riwayat Belajar & Sertifikat',
            summary: 'Unduh berkas PDF Sertifikat & Penghargaan resmi serta tinjau ulang kursus yang telah Anda selesaikan.'
        },
        'riwayat-detail': {
            title: 'Detail Riwayat Pelatihan',
            summary: 'Tinjau kembali materi, aktivitas belajar, dan hasil evaluasi pada kursus yang sudah tuntas.'
        },
        'notifikasi': {
            title: 'Kotak Masuk Notifikasi',
            summary: 'Baca pengumuman resmi dari Admin/Pengajar, saring berdasarkan tanggal, dan tandai pesan sudah dibaca.'
        },
        'forum': {
            title: 'Forum Diskusi Sesi',
            summary: 'Buat topik pertanyaan baru, lampirkan gambar pendukung, beri tanda suka, dan ikuti balasan diskusi secara langsung.'
        },
        'profil': {
            title: 'Profil & Pengaturan Siswa',
            summary: 'Lihat identitas karyawan, ubah tema tampilan (Normal/Gelap), buka Pusat Bantuan FAQ & Live Chat, atau keluar akun.'
        },
        'profil-detail': {
            title: 'Detail Identitas Karyawan',
            summary: 'Periksa data NPK, nama, email, dan departemen Anda, atau hubungi Admin melalui Live Chat jika ada perubahan data.'
        },
        'profil-pengaturan': {
            title: 'Pengaturan Akun',
            summary: 'Kelola preferensi tampilan dan pengaturan akun Portal Siswa Anda.'
        },
        'profil-bantuan': {
            title: 'Pusat Bantuan FAQ & Live Chat',
            summary: 'Cari jawaban cepat pada daftar FAQ atau berkomunikasi langsung dengan Admin melalui Live Chat.'
        }
    };

    var STUDENT_GUIDES = {
        'beranda': [
            {
                label: 'Navigasi Utama Siswa',
                element: '#bottomNavBar',
                popover: {
                    title: '1. Menu Navigasi Portal Siswa',
                    description: 'Gunakan bilah navigasi ini untuk berpindah cepat antara Beranda, Katalog Course, Nilai, Riwayat & Sertifikat, Notifikasi, dan Profil.',
                    side: 'responsive-nav',
                    align: 'start'
                }
            },
            {
                label: 'Pencarian Course Saya',
                element: '#dashboardCourseSearch, #dashboardCourseSearchMobile',
                popover: {
                    title: '2. Pencarian Course Aktif',
                    description: 'Ketik judul atau kode pelatihan untuk mencari kursus yang sedang Anda ikuti di beranda.',
                    side: 'bottom',
                    align: 'start'
                }
            },
            {
                label: 'Filter Status (Semua / Aktif / Selesai)',
                element: '.lms-tab-bar',
                popover: {
                    title: '3. Tab Filter Status Course',
                    description: 'Klik tab Semua, Aktif, atau Selesai untuk menyaring daftar kursus Anda secara instan.',
                    side: 'bottom',
                    align: 'start'
                }
            },
            {
                label: 'Daftar Course & Lanjut Belajar',
                element: '#course-results',
                popover: {
                    title: '4. Kartu Course & Tombol Lanjutkan Belajar',
                    description: 'Pantau persentase progres setiap kursus, lalu tekan Lanjutkan Belajar untuk langsung membuka ruang materi.',
                    side: 'top',
                    align: 'center'
                }
            }
        ],
        'courses': [
            {
                label: 'Pencarian & Filter',
                element: '.dls-course-list-heading',
                popover: {
                    title: '1. Pencarian & Filter Kategori Course',
                    description: 'Cari judul kursus atau gunakan filter Semua, Pilihan, dan Wajib untuk menemukan pelatihan yang sesuai dengan kebutuhan Anda.',
                    side: 'bottom',
                    align: 'start'
                }
            },
            {
                label: 'Chip Kategori Cepat',
                element: '#chip-container',
                popover: {
                    title: '2. Filter Cepat Status & Kategori',
                    description: 'Pilih chip kategori untuk menyaring daftar katalog kursus secara instan tanpa memuat ulang halaman.',
                    side: 'bottom',
                    align: 'start'
                }
            },
            {
                label: 'Kartu Katalog Course',
                element: '#course-results',
                popover: {
                    title: '3. Daftar Kartu Katalog Course',
                    description: 'Setiap kartu menampilkan kategori, jumlah materi, dan status akses. Klik kursus untuk membuka ruang belajar atau mengajukan permintaan akses.',
                    side: 'top',
                    align: 'center'
                }
            }
        ],
        'course-detail': [
            {
                label: 'Banner & Identitas Course',
                element: '.dls-course-hero',
                popover: {
                    title: '1. Banner & Identitas Course',
                    description: 'Menampilkan foto sampul, judul pelatihan, kategori, serta nama trainer pengampu pada kursus ini.',
                    side: 'bottom',
                    align: 'start'
                }
            },
            {
                label: 'Progres Belajar (Gate 100%)',
                element: '.dls-course-summary',
                popover: {
                    title: '2. Ringkasan Progres Belajar',
                    description: 'Menampilkan persentase materi yang sudah Anda selesaikan. Pastikan mencapai 100% agar evaluasi Quiz dapat dibuka.',
                    side: 'bottom',
                    align: 'start'
                }
            },
            {
                label: 'Tab Materi, Quiz & Forum',
                element: '.dls-course-tabs',
                popover: {
                    title: '3. Tab Materi, Quiz, Forum, Riwayat & Capaian',
                    description: 'Coba klik tab untuk berpindah antara ruang baca/tonton Materi, pengerjaan Quiz, Forum diskusi sesi, Riwayat, dan Capaian Anda.',
                    side: 'bottom',
                    align: 'center'
                }
            }
        ],
        'course-access': [
            {
                label: 'Form Permintaan Akses',
                element: '.dls-access-layout',
                popover: {
                    title: 'Formulir Permintaan Akses (Enrollment)',
                    description: 'Lihat status persetujuan akses kursus Anda saat ini, tulis alasan pengajuan bila diperlukan, lalu kirim permintaan ke Admin.',
                    side: 'top',
                    align: 'start'
                }
            }
        ],
        'filters': [
            {
                label: 'Input Pencarian & Reset',
                element: '#courseSearch',
                popover: {
                    title: '1. Pencarian & Reset Filter',
                    description: 'Ketik kata kunci judul atau topik pelatihan untuk menyaring katalog, atau tekan Reset untuk mengembalikan semua filter.',
                    side: 'bottom',
                    align: 'start'
                }
            },
            {
                label: 'Urutkan Hasil',
                element: '#sort-select',
                popover: {
                    title: '2. Urutkan Hasil Pencarian',
                    description: 'Atur urutan tampilan kursus sesuai preferensi Anda sebelum memilih pelatihan.',
                    side: 'bottom',
                    align: 'end'
                }
            },
            {
                label: 'Daftar Hasil',
                element: '#course-results',
                popover: {
                    title: '3. Daftar Hasil Pelatihan',
                    description: 'Menampilkan seluruh kursus yang cocok dengan kriteria pencarian dan filter Anda.',
                    side: 'top',
                    align: 'start'
                }
            }
        ],
        'nilai': [
            {
                label: 'Pencarian Nilai',
                element: 'main section:first-of-type',
                popover: {
                    title: '1. Pencarian & Ringkasan Nilai Saya',
                    description: 'Gunakan kolom pencarian untuk mencari nama kursus tertentu dan melihat ringkasan nilai evaluasi Anda.',
                    side: 'bottom',
                    align: 'start'
                }
            },
            {
                label: 'Tabel Rekap Nilai',
                element: '.dls-grade-table-scroll',
                popover: {
                    title: '2. Tabel Rekapitulasi Nilai Quiz & Progres',
                    description: 'Menampilkan daftar kursus, persentase penyelesaian materi, skor evaluasi quiz, serta status kelulusan Anda.',
                    side: 'top',
                    align: 'center'
                }
            }
        ],
        'riwayat': [
            {
                label: 'Panel Sertifikat PDF',
                element: '#student-certificates-section',
                popover: {
                    title: '1. Panel Sertifikat & Penghargaan Diterima',
                    description: 'Seluruh sertifikat resmi yang dikirim oleh Pengajar atau Admin muncul di sini dan dapat langsung Anda lihat atau unduh dalam format PDF.',
                    side: 'bottom',
                    align: 'start'
                }
            },
            {
                label: 'Daftar Riwayat Selesai',
                element: '#course-results',
                popover: {
                    title: '2. Daftar Riwayat Pelatihan Selesai',
                    description: 'Menampilkan daftar kursus yang telah Anda selesaikan. Klik detail riwayat untuk melihat kembali materi, capaian, dan aktivitas belajar.',
                    side: 'top',
                    align: 'start'
                }
            }
        ],
        'riwayat-detail': [
            {
                label: 'Detail Kursus Selesai',
                element: '.dls-course-hero',
                popover: {
                    title: '1. Detail Kursus Selesai',
                    description: 'Menampilkan informasi utama kursus yang telah Anda tuntaskan.',
                    side: 'bottom',
                    align: 'start'
                }
            },
            {
                label: 'Tab Tinjauan Riwayat',
                element: '.dls-course-tabs',
                popover: {
                    title: '2. Tab Tinjauan Materi, Aktivitas & Capaian',
                    description: 'Gunakan tab ini untuk meninjau ulang materi, riwayat aktivitas belajar, maupun ringkasan hasil evaluasi pada kursus tersebut.',
                    side: 'bottom',
                    align: 'center'
                }
            }
        ],
        'notifikasi': [
            {
                label: 'Filter & Tandai Dibaca',
                element: '#notificationSearch',
                popover: {
                    title: '1. Pencarian, Filter Tanggal & Tandai Dibaca',
                    description: 'Cari pengumuman berdasarkan kata kunci atau tanggal, lalu gunakan tombol Tandai Semua Dibaca untuk membersihkan notifikasi yang belum dibaca.',
                    side: 'bottom',
                    align: 'start'
                }
            },
            {
                label: 'Daftar Pengumuman',
                element: '#studentNotifications',
                popover: {
                    title: '2. Daftar Pengumuman & Pengingat Belajar',
                    description: 'Menampilkan pesan resmi dari Admin dan Pengajar mengenai kelas baru, pengingat evaluasi, atau pembaruan sistem.',
                    side: 'top',
                    align: 'start'
                }
            }
        ],
        'forum': [
            {
                label: 'Buat Topik / Balasan',
                element: 'main form, #forum-title',
                popover: {
                    title: '1. Buat Topik atau Kirim Balasan Diskusi',
                    description: 'Ajukan pertanyaan terkait materi sesi kepada pengajar atau rekan peserta lainnya, lengkap dengan dukungan lampiran gambar.',
                    side: 'bottom',
                    align: 'start'
                }
            },
            {
                label: 'Daftar Utas Diskusi',
                element: '[data-forum-view], main section',
                popover: {
                    title: '2. Daftar Topik & Utas Percakapan',
                    description: 'Baca pembahasan yang sudah dibuat di sesi ini, berikan tanda suka (like), dan ikuti balasan secara langsung.',
                    side: 'top',
                    align: 'start'
                }
            }
        ],
        'profil': [
            {
                label: 'Kartu Identitas Akun',
                element: 'main section:first-of-type',
                popover: {
                    title: '1. Kartu Identitas & Ringkasan Akun',
                    description: 'Menampilkan nama, NPK karyawan, dan status akun Anda pada DENSO Learning Portal.',
                    side: 'bottom',
                    align: 'start'
                }
            },
            {
                label: 'Menu Detail & Bantuan',
                element: 'main section:nth-of-type(2)',
                popover: {
                    title: '2. Menu Detail Profil, Pengaturan & Pusat Bantuan',
                    description: 'Buka Detail Profil untuk melihat data lengkap karyawan, Pengaturan untuk preferensi akun, atau Pusat Bantuan & FAQ untuk menghubungi Admin.',
                    side: 'top',
                    align: 'start'
                }
            },
            {
                label: 'Tema & Keluar Akun',
                element: '#btn-toggle-tema-profil, #btn-keluar',
                popover: {
                    title: '3. Pengaturan Tema Tampilan & Keluar Akun',
                    description: 'Ubah tema tampilan antara mode Normal dan Gelap (Dark Mode), atau tekan Keluar untuk mengakhiri sesi login Anda dengan aman.',
                    side: 'top',
                    align: 'start'
                }
            }
        ],
        'profil-detail': [
            {
                label: 'Identitas Karyawan',
                element: 'main section:first-of-type',
                popover: {
                    title: '1. Informasi Identitas Karyawan',
                    description: 'Menampilkan data NPK, nama lengkap, email, departemen, dan jabatan Anda yang terdaftar di sistem.',
                    side: 'bottom',
                    align: 'start'
                }
            },
            {
                label: 'Tombol Hubungi Admin',
                element: '#btnProfilHubungiAdmin, main section:last-of-type',
                popover: {
                    title: '2. Bantuan & Hubungi Admin',
                    description: 'Apabila terdapat perbedaan data akun atau kendala akses pelatihan, gunakan tombol Hubungi Admin untuk membuka Live Chat bantuan.',
                    side: 'top',
                    align: 'start'
                }
            }
        ],
        'profil-pengaturan': [
            {
                label: 'Preferensi Akun',
                element: 'main section:first-of-type',
                popover: {
                    title: 'Pengaturan Preferensi Akun Siswa',
                    description: 'Atur preferensi tampilan dan konfigurasi akun Anda pada halaman pengaturan ini.',
                    side: 'top',
                    align: 'start'
                }
            }
        ],
        'profil-bantuan': [
            {
                label: 'Pencarian FAQ',
                element: '#faqSearch',
                popover: {
                    title: '1. Pencarian Pertanyaan Umum (FAQ)',
                    description: 'Ketik kata kunci kendala Anda (misalnya login, quiz, materi, atau sertifikat) untuk menemukan solusi cepat.',
                    side: 'bottom',
                    align: 'start'
                }
            },
            {
                label: 'Daftar Topik FAQ',
                element: 'main section:nth-of-type(2)',
                popover: {
                    title: '2. Daftar Panduan & Jawaban FAQ',
                    description: 'Klik setiap topik pertanyaan untuk membaca langkah penyelesaian masalah secara mandiri.',
                    side: 'top',
                    align: 'start'
                }
            },
            {
                label: 'Live Chat Admin',
                element: 'main section:last-of-type',
                popover: {
                    title: '3. Pusat Bantuan & Live Chat Admin',
                    description: 'Jika kendala belum terjawab di FAQ, gunakan layanan Live Chat Admin untuk berkomunikasi langsung dengan tim pengelola LMS.',
                    side: 'top',
                    align: 'start'
                }
            }
        ]
    };

    function getActiveTab() {
        var urlParams = new URLSearchParams(window.location.search);
        var route = urlParams.get('route') || '/student';
        if (route.indexOf('/courses/detail') !== -1) return 'course-detail';
        if (route.indexOf('/courses/access') !== -1) return 'course-access';
        if (route.indexOf('/courses') !== -1) return 'courses';
        if (route.indexOf('/filters') !== -1) return 'filters';
        if (route.indexOf('/nilai') !== -1) return 'nilai';
        if (route.indexOf('/riwayat/detail') !== -1) return 'riwayat-detail';
        if (route.indexOf('/riwayat') !== -1) return 'riwayat';
        if (route.indexOf('/notifikasi') !== -1) return 'notifikasi';
        if (route.indexOf('/forum') !== -1) return 'forum';
        if (route.indexOf('/profil/detail') !== -1) return 'profil-detail';
        if (route.indexOf('/profil/pengaturan') !== -1) return 'profil-pengaturan';
        if (route.indexOf('/profil/bantuan') !== -1) return 'profil-bantuan';
        if (route.indexOf('/profil') !== -1) return 'profil';
        return 'beranda';
    }

    function isElementVisible(el) {
        if (!el) return false;
        var style = window.getComputedStyle(el);
        if (style.display === 'none' || style.visibility === 'hidden' || parseFloat(style.opacity) === 0) {
            return false;
        }
        var rect = el.getBoundingClientRect();
        return rect.width > 0 && rect.height > 0;
    }

    function findVisibleElement(selectorOrEl) {
        if (!selectorOrEl) return null;
        if (typeof selectorOrEl !== 'string') {
            return isElementVisible(selectorOrEl) ? selectorOrEl : null;
        }
        var nodes = document.querySelectorAll(selectorOrEl);
        for (var i = 0; i < nodes.length; i++) {
            if (isElementVisible(nodes[i])) return nodes[i];
        }
        return null;
    }

    function resolveResponsiveSide(side) {
        var isMobile = window.innerWidth < 1024;
        if (side === 'responsive-nav') {
            return isMobile ? 'top' : 'right';
        }
        if (isMobile && (side === 'left' || side === 'right')) {
            return 'bottom';
        }
        return side || 'bottom';
    }

    function getAvailableSteps(activeTab) {
        var raw = STUDENT_GUIDES[activeTab] || STUDENT_GUIDES['beranda'];
        var steps = [];
        for (var i = 0; i < raw.length; i++) {
            var s = raw[i];
            var el = s.element ? findVisibleElement(s.element) : null;
            if (!s.element || el) {
                steps.push({
                    label: s.label,
                    element: el || undefined,
                    popover: {
                        title: s.popover.title,
                        description: s.popover.description,
                        side: resolveResponsiveSide(s.popover.side),
                        align: s.popover.align || 'start'
                    }
                });
            }
        }
        if (!steps.length) {
            var secs = Array.prototype.slice.call(document.querySelectorAll('main section, main form'), 0, 6);
            secs.forEach(function(sec, idx) {
                if (!isElementVisible(sec)) return;
                var h = sec.querySelector('h1, h2, h3');
                var label = (h && h.textContent) ? h.textContent.trim() : ('Bagian ' + (steps.length + 1));
                steps.push({
                    label: label,
                    element: sec,
                    popover: {
                        title: label,
                        description: 'Bagian ini menampilkan informasi, filter, atau fitur utama pada halaman aktif.',
                        side: 'top',
                        align: 'start'
                    }
                });
            });
        }
        return steps;
    }

    window.startStudentUserGuide = function(startIndex, includeBubble) {
        if (typeof window.driver === 'undefined' || typeof window.driver.js === 'undefined') {
            console.warn('Driver.js belum siap atau gagal dimuat.');
            return;
        }

        var activeTab = getActiveTab();
        var steps = getAvailableSteps(activeTab).slice();

        if (includeBubble !== false && document.getElementById('tour-student-guide-btn')) {
            steps.push({
                label: 'Tombol Panduan (?)',
                element: '#tour-student-guide-btn',
                popover: {
                    title: 'Tombol Panduan Interaktif (?)',
                    description: 'Tombol ini dapat digeser ke mana saja. Klik kapan pun untuk membuka daftar bagian halaman ini atau melompat langsung ke bagian tertentu.',
                    side: 'top',
                    align: 'end'
                }
            });
        }

        if (!steps.length) return;
        var idx = typeof startIndex === 'number' ? Math.min(Math.max(0, startIndex), steps.length - 1) : 0;

        var driverInstance = window.driver.js.driver({
            showProgress: true,
            animate: true,
            allowClose: true,
            disableActiveInteraction: false,
            overlayColor: 'rgba(15, 23, 42, 0.72)',
            nextBtnText: 'Lanjut &rarr;',
            prevBtnText: '&larr; Kembali',
            doneBtnText: 'Selesai Panduan',
            progressText: 'Langkah {{current}} dari {{total}}',
            onHighlightStarted: function(element) {
                if (element && typeof element.scrollIntoView === 'function') {
                    try {
                        element.scrollIntoView({ behavior: 'smooth', block: 'center', inline: 'nearest' });
                    } catch (e) {}
                }
            },
            steps: steps
        });

        driverInstance.drive(idx);
    };

    function initStudentFloatingBubble() {
        if (document.getElementById('tour-student-guide-btn')) return;

        var bubble = document.createElement('div');
        bubble.id = 'tour-student-guide-btn';
        bubble.setAttribute('role', 'button');
        bubble.setAttribute('tabindex', '0');
        bubble.setAttribute('aria-label', 'Buka Panduan Interaktif');
        bubble.title = 'Panduan Interaktif Halaman (Klik / Geser)';
        bubble.className = 'dls-student-floating-guide-bubble';
        bubble.innerHTML =
            '<div class="dls-bubble-inner">' +
                '<span class="dls-bubble-symbol">?</span>' +
            '</div>';
        document.body.appendChild(bubble);

        var menu = document.createElement('div');
        menu.id = 'dls-student-guide-menu';
        menu.style.cssText = [
            'display:none',
            'position:fixed',
            'z-index:10050',
            'width:min(330px, calc(100vw - 24px))',
            'background:#ffffff',
            'color:#0f172a',
            'border:1px solid #cbd5e1',
            'border-radius:12px',
            'padding:14px',
            'box-shadow:0 16px 36px rgba(15,23,42,0.22)',
            'font-family:inherit'
        ].join(';');
        document.body.appendChild(menu);

        function closeMenu() {
            menu.style.display = 'none';
        }

        function openMenu() {
            var activeTab = getActiveTab();
            var meta = PAGE_META[activeTab] || { title: 'Portal Siswa', summary: 'Panduan bagian halaman aktif.' };
            var isTeacherMode = Boolean(document.querySelector('[action*="teacher-mode"], [href*="switch-to-teacher"]'));
            var roleText = isTeacherMode ? 'Mode Simulasi Teacher' : 'Peserta (Student)';
            var steps = getAvailableSteps(activeTab);

            var rect = bubble.getBoundingClientRect();
            var isSmallScreen = window.innerWidth < 640;
            if (isSmallScreen) {
                menu.style.left = '12px';
                menu.style.right = '12px';
                menu.style.width = 'auto';
                menu.style.top = 'auto';
                menu.style.bottom = Math.max(84, window.innerHeight - rect.top + 10) + 'px';
            } else {
                var openLeft = rect.left > window.innerWidth / 2;
                var openUp = rect.top > window.innerHeight / 2;
                menu.style.width = 'min(330px, calc(100vw - 24px))';
                menu.style.left = openLeft ? 'auto' : Math.max(12, rect.left) + 'px';
                menu.style.right = openLeft ? Math.max(12, window.innerWidth - rect.right) + 'px' : 'auto';
                menu.style.top = openUp ? 'auto' : Math.max(12, rect.bottom + 10) + 'px';
                menu.style.bottom = openUp ? Math.max(12, window.innerHeight - rect.top + 10) + 'px' : 'auto';
            }

            var listHtml = steps.map(function(s, i) {
                var lbl = (s.label || (s.popover && s.popover.title) || ('Bagian ' + (i + 1))).replace(/</g, '&lt;');
                return '<button type="button" data-guide-step="' + i + '" style="display:flex;width:100%;align-items:center;justify-content:space-between;padding:7px 10px;margin-bottom:4px;border:1px solid #e2e8f0;border-radius:8px;background:#f8fafc;color:#0f172a;font-size:12px;font-weight:500;cursor:pointer;text-align:left;">' +
                    '<span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">' + lbl + '</span>' +
                    '<span style="margin-left:8px;color:#b91c1c;font-size:11px;font-weight:600;">Lihat &rarr;</span>' +
                    '</button>';
            }).join('');

            menu.innerHTML =
                '<div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;">' +
                    '<div>' +
                        '<span style="display:inline-block;background:#fee2e2;color:#b91c1c;font-size:11px;font-weight:700;padding:2px 8px;border-radius:6px;">' + roleText + '</span>' +
                        '<h3 style="margin:6px 0 0;font-size:14px;font-weight:700;">' + meta.title + '</h3>' +
                    '</div>' +
                    '<button type="button" id="dls-guide-close" aria-label="Tutup" style="border:none;background:transparent;font-size:14px;cursor:pointer;color:#64748b;padding:2px 6px;">&#10005;</button>' +
                '</div>' +
                '<p style="margin:6px 0 10px;font-size:12px;line-height:1.45;color:#475569;">' + meta.summary + '</p>' +
                (steps.length ? '<div style="font-size:11px;font-weight:700;text-transform:uppercase;color:#64748b;margin-bottom:6px;">Sorot Bagian Halaman (Interaktif)</div><div style="max-height:185px;overflow-y:auto;">' + listHtml + '</div>' : '') +
                '<button type="button" id="dls-guide-start-all" style="margin-top:8px;width:100%;padding:8px 12px;border:none;border-radius:8px;background:#dc2626;color:#ffffff;font-size:12px;font-weight:700;cursor:pointer;">Mulai Tur Lengkap Halaman Ini</button>';

            menu.style.display = 'block';

            var closeBtn = document.getElementById('dls-guide-close');
            if (closeBtn) closeBtn.addEventListener('click', closeMenu);

            var startAllBtn = document.getElementById('dls-guide-start-all');
            if (startAllBtn) {
                startAllBtn.addEventListener('click', function() {
                    closeMenu();
                    window.startStudentUserGuide(0, true);
                });
            }

            Array.prototype.forEach.call(menu.querySelectorAll('[data-guide-step]'), function(btn) {
                btn.addEventListener('click', function() {
                    var idx = parseInt(btn.getAttribute('data-guide-step'), 10) || 0;
                    closeMenu();
                    window.startStudentUserGuide(idx, false);
                });
            });
        }

        function toggleMenu() {
            if (menu.style.display === 'none' || !menu.style.display) {
                openMenu();
            } else {
                closeMenu();
            }
        }

        document.addEventListener('pointerdown', function(e) {
            if (menu.style.display === 'block' && !menu.contains(e.target) && !bubble.contains(e.target)) {
                closeMenu();
            }
        });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeMenu();
        });

        var savedPos = null;
        try {
            var raw = localStorage.getItem('dls_student_guide_pos');
            if (raw) savedPos = JSON.parse(raw);
        } catch(e) {}

        function clamp(x, y) {
            var m = 12;
            var w = bubble.offsetWidth || 48;
            var h = bubble.offsetHeight || 48;
            var maxX = Math.max(m, window.innerWidth - w - m);
            var maxY = Math.max(m, window.innerHeight - h - m);
            return {
                x: Math.min(Math.max(m, x), maxX),
                y: Math.min(Math.max(m, y), maxY)
            };
        }

        if (savedPos && typeof savedPos.x === 'number' && typeof savedPos.y === 'number') {
            var c = clamp(savedPos.x, savedPos.y);
            bubble.style.left = c.x + 'px';
            bubble.style.top = c.y + 'px';
            bubble.style.right = 'auto';
            bubble.style.bottom = 'auto';
        }

        var drag = null;

        bubble.addEventListener('pointerdown', function(e) {
            if (e.button !== 0 && e.pointerType === 'mouse') return;
            var rect = bubble.getBoundingClientRect();
            drag = {
                id: e.pointerId,
                startX: e.clientX,
                startY: e.clientY,
                initialLeft: rect.left,
                initialTop: rect.top,
                hasMoved: false
            };
            try { bubble.setPointerCapture(e.pointerId); } catch(err) {}
        });

        bubble.addEventListener('pointermove', function(e) {
            if (!drag || drag.id !== e.pointerId) return;
            var dx = e.clientX - drag.startX;
            var dy = e.clientY - drag.startY;
            if (!drag.hasMoved && (Math.abs(dx) > 4 || Math.abs(dy) > 4)) {
                drag.hasMoved = true;
                bubble.classList.add('is-dragging');
                closeMenu();
            }
            if (drag.hasMoved) {
                var c = clamp(drag.initialLeft + dx, drag.initialTop + dy);
                bubble.style.left = c.x + 'px';
                bubble.style.top = c.y + 'px';
                bubble.style.right = 'auto';
                bubble.style.bottom = 'auto';
            }
        });

        function endDrag(e) {
            if (!drag || drag.id !== e.pointerId) return;
            var moved = drag.hasMoved;
            try { if (bubble.hasPointerCapture(e.pointerId)) bubble.releasePointerCapture(e.pointerId); } catch(err) {}
            bubble.classList.remove('is-dragging');
            drag = null;

            if (moved) {
                var rect = bubble.getBoundingClientRect();
                try {
                    localStorage.setItem('dls_student_guide_pos', JSON.stringify({ x: rect.left, y: rect.top }));
                } catch(err) {}
            } else {
                toggleMenu();
            }
        }

        bubble.addEventListener('pointerup', endDrag);
        bubble.addEventListener('pointercancel', endDrag);

        bubble.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                toggleMenu();
            }
        });

        window.addEventListener('resize', function() {
            var rect = bubble.getBoundingClientRect();
            var c = clamp(rect.left, rect.top);
            bubble.style.left = c.x + 'px';
            bubble.style.top = c.y + 'px';
            bubble.style.right = 'auto';
            bubble.style.bottom = 'auto';
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initStudentFloatingBubble);
    } else {
        initStudentFloatingBubble();
    }
})();
