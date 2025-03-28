// 註冊 Service Worker
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/AMO/sw.js')
            .then(registration => {
                console.log('ServiceWorker registration successful with scope: ', registration.scope);
            })
            .catch(err => {
                console.log('ServiceWorker registration failed: ', err);
            });
    });
}

// 添加"安裝"提示
let deferredPrompt;
window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
    // 可以在這裡添加自訂的"安裝"按鈕邏輯
});
