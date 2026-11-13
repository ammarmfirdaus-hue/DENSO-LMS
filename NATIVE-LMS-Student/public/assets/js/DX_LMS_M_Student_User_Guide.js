/**
 * DLS Student Interactive Tour & User Guide (Driver.js)
 * Panduan interaktif terintegrasi dan mendalam untuk Siswa DENSO Learning Portal.
 * Menggunakan tombol bubble "?" sederhana, profesional, dan dapat digeser (draggable).
 */
(function() {
    'use strict';

    var STUDENT_GUIDES = {
        'beranda': [
            {
                element: '#bottomNavBar',
                popover: {
                    title: 'Bilah Navigasi Utama Siswa',
                    description: 'Pusat akses cepat menuju seluruh menu penting: Beranda, Katalog Course, Buku Nilai, Riwayat Jam Pelatihan, dan Notifikasi.',
                    side: 'right',
                    align: 'start'
                }
            },
            {
                element: '.dls-welcome-hero',
                popover: {
                    title: 'Status & Target Belajar Harian',
                    description: 'Memantau progres capaian pembelajaran Anda, target jam pelatihan yang telah terpenuhi, serta pengingat modul terdekat.',
                    side: 'bottom',
                    align: 'start'
                }
            },
            {
                element: '#tour-student-guide-btn',
                popover: {
                    title: 'Tombol Bantuan Halaman (?)',
                    description: 'Tombol bubble ini dapat Anda geser ke posisi mana pun di layar. Tekan tombol ini kapan pun Anda membutuhkan penjelasan langkah cara pakai.',
                    side: 'top',
                    align: 'end'
                }
            }
        ],
        'courses': [
            {
                element: '#bottomNavBar',
                popover: {
                    title: 'Katalog Modul Pembelajaran',
                    description: 'Daftar silabus pelatihan yang ditugaskan atau terbuka untuk dipelajari guna meningkatkan kompetensi teknis di DENSO.',
                    side: 'right',
                    align: 'start'
                }
            },
            {
                element: '.dls-course-list-heading',
                popover: {
                    title: 'Pencarian & Filter Pelatihan',
                    description: 'Gunakan kolom pencarian judul atau tombol filter kategori untuk memilah course Wajib (Mandatory) dan Pilihan (Elective).',
                    side: 'bottom',
                    align: 'start'
                }
            },
            {
                element: '#course-results',
                popover: {
                    title: 'Kartu Pelatihan & Akses Materi',
                    description: 'Pilih modul untuk masuk ke sesi belajar. Anda dapat menyimak video presentasi, dokumen SOP materi, dan mengerjakan evaluasi quiz.',
                    side: 'top',
                    align: 'center'
                }
            }
        ],
        'nilai': [
            {
                element: '#bottomNavBar',
                popover: {
                    title: 'Rekapitulasi Nilai & Evaluasi',
                    description: 'Menu penilaian untuk meninjau riwayat skor ujian akhir quiz pada setiap silabus pelatihan yang Anda ikuti.',
                    side: 'right',
                    align: 'start'
                }
            },
            {
                element: '.dls-grade-table-scroll',
                popover: {
                    title: 'Tabel Nilai & Standar Kelulusan',
                    description: 'Menampilkan detail judul pelatihan, kode materi, perolehan nilai angka, dan persentase progres penyelesaian hingga tuntas 100%.',
                    side: 'top',
                    align: 'center'
                }
            }
        ],
        'riwayat': [
            {
                element: '#bottomNavBar',
                popover: {
                    title: 'Riwayat Pelatihan Selesai',
                    description: 'Daftar dokumentasi resmi seluruh kursus dan sesi pelatihan yang telah berhasil Anda selesaikan di DENSO Learning Portal.',
                    side: 'right',
                    align: 'start'
                }
            }
        ],
        'notifikasi': [
            {
                element: '#bottomNavBar',
                popover: {
                    title: 'Pusat Pemberitahuan Resmi',
                    description: 'Pemberitahuan instruktur mengenai jadwal kelas baru, pengingat tenggat batas waktu modul, dan pengumuman kedinasan.',
                    side: 'right',
                    align: 'start'
                }
            }
        ],
        'profil': [
            {
                element: '#bottomNavBar',
                popover: {
                    title: 'Profil & Pengaturan Akun',
                    description: 'Periksa data identitas NPK, perbarui kata sandi akun, atur preferensi tampilan (mode gelap/terang), atau ajukan bantuan teknis.',
                    side: 'right',
                    align: 'start'
                }
            }
        ]
    };

    window.startStudentUserGuide = function() {
        if (typeof window.driver === 'undefined' || typeof window.driver.js === 'undefined') {
            console.warn('Driver.js belum siap atau gagal dimuat.');
            return;
        }

        var urlParams = new URLSearchParams(window.location.search);
        var route = urlParams.get('route') || '/student';
        var activeTab = 'beranda';
        if (route.indexOf('/courses') !== -1) activeTab = 'courses';
        else if (route.indexOf('/nilai') !== -1) activeTab = 'nilai';
        else if (route.indexOf('/riwayat') !== -1) activeTab = 'riwayat';
        else if (route.indexOf('/notifikasi') !== -1) activeTab = 'notifikasi';
        else if (route.indexOf('/profil') !== -1) activeTab = 'profil';

        var steps = (STUDENT_GUIDES[activeTab] || STUDENT_GUIDES['beranda']).filter(function(s) {
            return !s.element || document.querySelector(s.element);
        });

        if (!steps.length) {
            steps = [
                {
                    element: '#bottomNavBar',
                    popover: {
                        title: 'Navigasi Portal Siswa',
                        description: 'Gunakan navigasi ini untuk mengakses seluruh materi, quiz, dan informasi pembelajaran Anda.',
                        side: 'right',
                        align: 'start'
                    }
                }
            ];
        }

        var driverInstance = window.driver.js.driver({
            showProgress: true,
            animate: true,
            allowClose: true,
            overlayColor: 'rgba(15, 23, 42, 0.70)',
            nextBtnText: 'Lanjut &rarr;',
            prevBtnText: '&larr; Kembali',
            doneBtnText: 'Selesai Panduan',
            progressText: 'Langkah {{current}} dari {{total}}',
            steps: steps
        });

        driverInstance.drive();
    };

    /**
     * Inisialisasi Floating Draggable Guide Bubble untuk Student Portal
     */
    function initStudentFloatingBubble() {
        if (document.getElementById('tour-student-guide-btn')) return;

        var bubble = document.createElement('div');
        bubble.id = 'tour-student-guide-btn';
        bubble.setAttribute('role', 'button');
        bubble.setAttribute('tabindex', '0');
        bubble.setAttribute('aria-label', 'Buka Panduan Bantuan');
        bubble.title = 'Panduan Halaman (Bisa Digeser)';
        bubble.className = 'dls-student-floating-guide-bubble';

        bubble.innerHTML = 
            '<div class="dls-bubble-inner">' +
                '<span class="dls-bubble-symbol">?</span>' +
            '</div>';

        document.body.appendChild(bubble);

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
                window.startStudentUserGuide();
            }
        }

        bubble.addEventListener('pointerup', endDrag);
        bubble.addEventListener('pointercancel', endDrag);

        bubble.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                window.startStudentUserGuide();
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
