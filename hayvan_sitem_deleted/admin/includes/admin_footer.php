<?php
// admin/includes/admin_footer.php
// Bu dosya, admin panelinin genel HTML kapanış etiketlerini ve
// tüm sayfalarda kullanılan JavaScript dosyalarını içerir.
// Her admin paneli sayfasının sonunda include edilmelidir.
?>

<script>
    // Dropdown açma/kapatma mantığı (eğer Tailwind için özel bir JS kütüphanesi kullanmıyorsanız)
    document.querySelectorAll('[data-dropdown-toggle]').forEach(button => {
        button.addEventListener('click', function() {
            const dropdownId = this.dataset.dropdownToggle;
            const dropdown = document.getElementById(dropdownId);
            dropdown.classList.toggle('hidden');
        });
    });

    // Sayfa dışına tıklayınca dropdown'ı kapatma
    document.addEventListener('click', function(event) {
        document.querySelectorAll('[id^="dropdown_"]').forEach(dropdown => {
            const button = document.querySelector(`[data-dropdown-toggle="${dropdown.id}"]`);
            if (dropdown && button && !dropdown.contains(event.target) && !button.contains(event.target)) {
                dropdown.classList.add('hidden');
            }
        });
    });

    // AJAX işlemleri ve SweetAlert2 entegrasyonu (talep durum güncelleme vs. için)
    document.querySelectorAll('.action-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const action = this.dataset.action;
            const talepId = this.dataset.id;
            let title = '';
            let text = '';
            let confirmButtonText = '';
            let showInput = false;
            let inputPlaceholder = '';

            if (action === 'onayla') {
                title = 'Talebi Onayla?';
                text = 'Bu talebi onaylamak istediğinizden emin misiniz?';
                confirmButtonText = 'Evet, Onayla!';
            } else if (action === 'reddet') {
                title = 'Talebi Reddet?';
                text = 'Bu talebi reddetmek istediğinizden emin misiniz?';
                confirmButtonText = 'Evet, Reddet!';
            } else if (action === 'tamamla') {
                title = 'Talebi Tamamla?';
                text = 'Bu talep tamamlandı olarak işaretlenecek ve ilgili ilan sahiplenildi olarak güncellenecektir. Emin misiniz?';
                confirmButtonText = 'Evet, Tamamla!';
            } else if (action === 'not_ekle') {
                title = 'Yönetici Notu Ekle';
                text = 'Bu talep için bir yönetici notu girin:';
                showInput = true;
                inputPlaceholder = 'Notunuzu buraya yazın...';
                confirmButtonText = 'Kaydet';
            } else {
                return; // Bilinmeyen eylem
            }

            Swal.fire({
                title: title,
                text: text,
                icon: action === 'onayla' || action === 'tamamla' ? 'question' : (action === 'reddet' ? 'warning' : 'info'),
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: confirmButtonText,
                cancelButtonText: 'İptal',
                input: showInput ? 'textarea' : null,
                inputPlaceholder: inputPlaceholder,
                inputAttributes: {
                    'aria-label': 'Yönetici Notu'
                },
                inputValidator: (value) => {
                    if (showInput && !value) {
                        return 'Not boş bırakılamaz!';
                    }
                },
                customClass: {
                    popup: 'swal2-popup',
                    title: 'swal2-title',
                    htmlContainer: 'swal2-html-container',
                    confirmButton: 'swal2-confirm',
                    cancelButton: 'swal2-cancel',
                    input: 'swal2-input'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    let formData = new FormData();
                    formData.append('talep_id', talepId);
                    formData.append('action', action);
                    if (showInput) {
                        formData.append('admin_note', result.value);
                    }

                    fetch('talep_durum_guncelle.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(res => {
                        if (res.status === 'success') {
                            Swal.fire({
                                title: 'Başarılı!', text: res.message, icon: 'success', timer: 2000, showConfirmButton: false,
                                customClass: { popup: 'swal2-popup', title: 'swal2-title', htmlContainer: 'swal2-html-container', confirmButton: 'swal2-confirm' }, buttonsStyling: false,
                            }).then(() => {
                                location.reload(); // Sayfayı yenile
                            });
                        } else {
                            Swal.fire({
                                title: 'Hata!', text: res.message, icon: 'error',
                                customClass: { popup: 'swal2-popup', title: 'swal2-title', htmlContainer: 'swal2-html-container', confirmButton: 'swal2-confirm' }, buttonsStyling: false,
                            });
                        }
                    })
                    .catch(error => {
                        console.error("AJAX error:", error);
                        Swal.fire({
                            icon: 'error', title: 'Bağlantı Hatası!', text: 'Sunucu ile iletişim kurulurken bir hata oluştu.',
                            customClass: { popup: 'swal2-popup', title: 'swal2-title', htmlContainer: 'swal2-html-container', confirmButton: 'swal2-confirm' }, buttonsStyling: false,
                        });
                    });
                }
            });
        });
    });
</script>

</body>
</html>