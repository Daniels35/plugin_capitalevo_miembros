jQuery(document).ready(function($) {

    // Modal
    const modal = $('#ds-member-modal');
    const closeModal = modal.find('.ds-close');
    const typedElement = document.getElementById('ds-typed');
    let typedInstance = null;

    // Al hacer clic en un miembro
    $('.team-member').on('click', function() {
        const memberId = $(this).data('member');

        // Obtenemos data del array que pasamos con wp_localize_script
        if (dsTeamMembersData && dsTeamMembersData[memberId]) {
            const memberData = dsTeamMembersData[memberId];

            // Si existe redirectUrl, redireccionar
            if (memberData.redirectUrl) {
                window.location.href = memberData.redirectUrl;
                return;
            }

            // Caso contrario, abrimos modal con Typed
            $('#ds-modal-member-name').text(memberData.name);

            // Destruimos cualquier instancia previa de Typed
            if (typedInstance) {
                typedInstance.destroy();
            }

            // Iniciamos Typed con el texto del miembro
            typedInstance = new Typed(typedElement, {
                strings: [memberData.typedText || ''],
                typeSpeed: 30,
                backSpeed: 85,
                loop: false
            });

            // Mostramos modal
            modal.show();
        }
    });

    // Cerrar modal
    closeModal.on('click', function() {
        modal.hide();
    });

    // Cerrar modal al hacer clic fuera de la caja
    $(window).on('click', function(e) {
        if ($(e.target).is(modal)) {
            modal.hide();
        }
    });

});
