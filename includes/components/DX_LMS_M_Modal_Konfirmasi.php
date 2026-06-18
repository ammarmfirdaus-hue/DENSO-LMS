<?php
/**
 * DLS Modal Konfirmasi Component
 * Sistem Pembelajaran Denso
 * 
 * Ref: pop_up_konfirmasi_kuis_background_tab_quiz_updated
 *      pop_up_konfirmasi_mulai_materi
 * 
 * Reusable modal with icon, title, description, and action buttons.
 * Controlled via JavaScript: showModal(type) / hideModal()
 */
?>
<!-- Modal Overlay -->
<div class="dls-modal-overlay" id="modalOverlay" onclick="if(event.target===this)hideModal()">
    <div class="dls-modal-content">
        <!-- Icon -->
        <div class="dls-modal-icon primary" id="modalIconContainer">
            <span class="material-symbols-outlined" id="modalIcon" style="font-size:28px;font-variation-settings:'FILL' 1;">assignment</span>
        </div>

        <!-- Title -->
        <h3 class="dls-modal-title" id="modalTitle">Mulai Kerjakan Quiz</h3>

        <!-- Description -->
        <p class="dls-modal-desc" id="modalDesc">Apakah Anda ingin memulai sesi quiz ini sekarang?</p>

        <!-- Action Buttons -->
        <div class="d-flex flex-column gap-2">
            <button class="dls-btn dls-btn-primary dls-btn-block dls-btn-rounded" id="modalBtnConfirm" onclick="hideModal()">
                Mulai
            </button>
            <button class="dls-btn dls-btn-outline dls-btn-block dls-btn-rounded" onclick="hideModal()">
                Batal
            </button>
        </div>
    </div>
</div>
