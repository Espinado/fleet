// =======================================
// Axios
// =======================================
import axios from "axios";

window.axios = axios;
window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

// =======================================
// Driver App Global Helpers
// =======================================
window.driver = {
    toast(message, type = "info") {
        window.dispatchEvent(
            new CustomEvent("driver-toast", {
                detail: { message, type },
            })
        );
    },
};
