let addingEmail = false;

function addEmail(element, name, accountType) {
    if(addingEmail) return;
    addingEmail = true;

    const emailsListBox = element.parentElement;
    const emailsList = emailsListBox.getElementsByClassName("emailsList")[0];

    const newEmail = document.createElement("div");
    newEmail.className = "addEmailsListItem";

    const newEmailInput = document.createElement("input");
    newEmailInput.type = "text";
    newEmailInput.style.background = "transparent";
    newEmailInput.style.border = "none";
    newEmailInput.style.padding = "0";
    newEmailInput.style.margin = "0";

    const newEmailConfirm = document.createElement("button");
    newEmailConfirm.innerText = "✅";
    newEmailConfirm.style.background = "transparent";
    newEmailConfirm.style.border = "none";
    newEmailConfirm.onclick = () => {submitNewEmail(name, accountType, newEmailInput, newEmail);};

    emailsList.appendChild(newEmail);
    newEmail.appendChild(newEmailInput);
    newEmail.appendChild(newEmailConfirm);

    newEmailInput.focus();

    newEmailInput.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            submitNewEmail(name, accountType, newEmailInput, newEmail);
        }
    });

    //remove add box when focus is lost
    const handleOutsideClick = (event) => {
        if (!newEmail.contains(event.target)) {
            newEmail.remove();
            addingEmail = false;
            document.removeEventListener('mousedown', handleOutsideClick);
        }
    };

    document.addEventListener('mousedown', handleOutsideClick);
}

async function submitNewEmail(name, accountType, emailInput, emailListItem) {
    const email = emailInput.value;
    const emailList = emailListItem.parentElement;

    console.log("submitting " + name + ", " + email);

    if(checkEmailStr(email)) {
        const response = await fetch('/php/addAssociatedEmail.php', {
            method: 'POST',
            body: new URLSearchParams({
                accountName: name,
                accountType,
                email: email
            })
        });

        const data = await response.json();

        if (data.success) {
            window.location.reload();
        } else {
            console.error(data.message);
        }
    } else {
        alert("Please enter a valid email.");
    }
}

function checkEmailStr(emailStr) {
    const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return pattern.test(emailStr) && emailStr.length <= 100;
}

async function deleteEmail(name, accountType, email) {
    if(confirm("Are you sure you want to remove " + email + "?") && name && email) {
        const response = await fetch('/php/deleteAssociatedEmail.php', {
            method: 'POST',
            body: new URLSearchParams({
                accountName: name,
                accountType,
                email: email
            })
        });

        const data = await response.json();

        if (data.success) {
            window.location.reload();
        } else {
            console.error(data.message);
        }
    }
}