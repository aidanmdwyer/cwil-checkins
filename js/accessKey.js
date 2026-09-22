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