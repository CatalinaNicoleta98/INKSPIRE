// Register page JS
const pwd = document.getElementById('registerPassword');
const pwdConfirm = document.getElementById('registerPasswordConfirm');

const reqLength = document.getElementById('req-length');
const reqUpper = document.getElementById('req-upper');
const reqLower = document.getElementById('req-lower');
const reqNumber = document.getElementById('req-number');
const reqSpecial = document.getElementById('req-special');
const matchHint = document.getElementById('passwordMatchHint');

function setReq(el, ok) {
    if (!el) return;
    el.classList.toggle('text-green-600', ok);
    el.classList.toggle('text-gray-500', !ok);
}

function updateStrength() {
    const val = pwd.value;

    setReq(reqLength, val.length >= 8);
    setReq(reqUpper, /[A-Z]/.test(val));
    setReq(reqLower, /[a-z]/.test(val));
    setReq(reqNumber, /[0-9]/.test(val));
    setReq(reqSpecial, /[^A-Za-z0-9]/.test(val));
}

function updateMatch() {
    const v1 = pwd.value;
    const v2 = pwdConfirm.value;

    if (!v1 && !v2) {
        matchHint.textContent = "";
        matchHint.className = "text-xs text-gray-500";
        return;
    }

    if (v1 === v2) {
        matchHint.textContent = "Passwords match.";
        matchHint.className = "text-xs text-green-600";
    } else {
        matchHint.textContent = "Passwords do not match.";
        matchHint.className = "text-xs text-red-500";
    }
}

if (pwd) pwd.addEventListener("input", () => { updateStrength(); updateMatch(); });
if (pwdConfirm) pwdConfirm.addEventListener("input", updateMatch);

// Terms Modal
const termsModal = document.getElementById("termsModal");
const openTerms = document.getElementById("openTermsModal");
const closeTerms = document.getElementById("closeTermsModal");

if (openTerms) openTerms.onclick = () => { termsModal.classList.remove("hidden"); termsModal.classList.add("flex"); };
if (closeTerms) closeTerms.onclick = () => { termsModal.classList.add("hidden"); termsModal.classList.remove("flex"); };