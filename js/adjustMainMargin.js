function adjustMainMargin() {
    const header = document.querySelector("header");
    const main = document.querySelector("main");
    const tableMenu = document.getElementById('tableMenu') ?? false;

    let mainMargin = header.offsetHeight
    if(tableMenu) {
        tableMenu.style.display = 'flex';
        tableMenu.style.top = header.offsetHeight + "px";
        mainMargin += tableMenu.offsetHeight + 15;
    } else {
        mainMargin += 30;
    }
    main.style.marginTop = mainMargin + "px";

    if(typeof editForm !== "undefined" && editForm) {
        editForm.style.top = mainMargin + 20 + "px";
    }
    if(typeof qrForm !== "undefined" && qrForm) {
        qrForm.style.top = mainMargin + "px";
    }
    if(typeof selectSubmits !== "undefined" && selectSubmits) {
        selectSubmits.style.top = mainMargin + 20 + "px";
    }
}

window.addEventListener("load", adjustMainMargin);
window.addEventListener("resize", adjustMainMargin);