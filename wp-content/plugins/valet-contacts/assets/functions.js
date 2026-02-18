function copyContactEmail(e, email) {
    e.preventDefault();
    navigator.clipboard.writeText(email).then(() => {
        e.target.style.color = '#3DEC55';
        setTimeout(() => {
            e.target.style.color = '';
        }, 1000);
    }).catch(err => {
        console.error("Failed to copy: ", err);
    });
}

function editContact(e, contact_id) {
    e.preventDefault();
    const editContactTab = document.getElementById('edit-contact');
    const editContactId = document.getElementById('edit_contact_id');
    const contact = e.target.closest('.single-contact');
    if(contact) {
        const contactFirstName = contact.querySelector('.contact-first-name').innerText;
        const contactLastName = contact.querySelector('.contact-last-name').innerText;
        const contactEmail = contact.querySelector('.contact-email').innerText;
        const contactPass = contact.querySelector('.contact-password').innerText;

        editContactId.value = contact_id;
        document.getElementById('edit_contact_first_name').value = contactFirstName; document.getElementById('edit_contact_last_name').value = contactLastName;
        document.getElementById('edit_contact_email').value = contactEmail; 				 document.getElementById('edit_contact_password').value = contactPass;
    }

    document.getElementById('client-page-new-edit-contact').innerText = 'Edit Contact';

    Array.from(document.getElementsByClassName('tab-pane')).forEach((tab) => {
        if(tab.classList.contains('brx-open')) {
            tab.classList.remove('brx-open')
        }
    });
    document.querySelector('.tab-title.brx-open').classList.remove('brx-open');
    if(!editContactTab.classList.contains('brx-open')) {
        editContactTab.classList.add('brx-open')
    }
}

function addNewContact(e) {
    e.preventDefault();
    const editContactTab = document.getElementById('edit-contact');
    document.getElementById('edit_contact_id').value = '';
    document.getElementById('edit_contact_first_name').value = '';
    document.getElementById('edit_contact_last_name').value = '';
    document.getElementById('edit_contact_email').value = ''; 				 document.getElementById('edit_contact_password').value = '';

    document.getElementById('client-page-new-edit-contact').innerText = 'New Contact';

    Array.from(document.getElementsByClassName('tab-pane')).forEach((tab) => {
        if(tab.classList.contains('brx-open')) {
            tab.classList.remove('brx-open')
        }
    });
    document.querySelector('.tab-title.brx-open').classList.remove('brx-open');
    if(!editContactTab.classList.contains('brx-open')) {
        editContactTab.classList.add('brx-open')
    }
}

function openContactDeleteModal(e, id) {
    e.preventDefault();
    const deleteModal = document.getElementById('contact-delete-modal');
    const deleteModalContactIdInput = document.getElementById('delete-modal-contact-id');
    deleteModalContactIdInput.value = id;
    if(!deleteModal.classList.contains('opened')) {
        deleteModal.classList.add('opened');
    }
}

function closeDeleteModal(e) {
    e.preventDefault();
    const deleteModal = document.getElementById('contact-delete-modal');
    if(deleteModal.classList.contains('opened')) {
        deleteModal.classList.remove('opened');
    }
}