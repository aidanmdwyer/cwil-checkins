const refreshButton = document.getElementById('refreshButton');
const buildingsTable = document.getElementById('buildingsTable');
const editForm = document.getElementById('editForm');
const qrTemplate = document.getElementById('qrTemplate');
const qrForm = document.getElementById('qrForm');
const tableSelectedForm = document.getElementById('tableSelectedForm');
const selectSubmits = document.getElementById('selectSubmits');
const selectedBuildingsText = document.getElementById('selectedBuildings');
const qrContainer = document.getElementById('qrcode');
const qrPreview = document.getElementById('qrcodePreview');
const qrPreviewTitle = document.getElementById('qrPreviewTitle');
const qrPreviewIC = document.getElementById('qrPreviewIC');
let contractorList = [];
let selectedIds = [];

let accessKey = null;

//promise that resolves once we first have a token
let accessKeyReady = getNewAccessKey();

function getNewAccessKey() {
    return fetch('/php/getKey.php')
        .then(res => res.json())
        .then(data => {
            accessKey = data.key;
            return accessKey;
        });
}

//reissue token when tab is focused (if laptop was closed then opened later, etc.)
window.addEventListener("focus", getNewAccessKey);

//reissue token every 14 minutes (before expiry)
setInterval(() => {
    getNewAccessKey().catch(err => {
        console.error("Failed to refresh JWT:", err);
    });
}, 14 * 60 * 1000); // 14 minutes