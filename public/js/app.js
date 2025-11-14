document.addEventListener("DOMContentLoaded", () => {
    const toastEl = document.getElementById("toast-success");
    if (toastEl && window.bootstrap) {
        const toast = bootstrap.Toast.getOrCreateInstance(toastEl);
        toast.show();
    }
});
