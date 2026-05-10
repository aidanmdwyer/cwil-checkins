function fillSelectMenu(id, data, set = null) {
    const menu = document.getElementById(id);
    menu.innerHTML = '';

    let option1 = document.createElement('option');
    option1.textContent = '---';
    menu.appendChild(option1);
    data.forEach(item => {
        const option = document.createElement('option');
        option.textContent = item;

        if(set && option.textContent === set) {
            option.selected = true;
        }

        menu.appendChild(option);
    });
}