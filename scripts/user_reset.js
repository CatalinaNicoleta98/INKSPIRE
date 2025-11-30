// Reset-password validation
const resetPwd = document.getElementById('resetPasswordInput');
const resetConfirm = document.getElementById('resetPasswordConfirm');

const resetReqLength = document.getElementById('reset-req-length');
const resetReqUpper = document.getElementById('reset-req-upper');
const resetReqLower = document.getElementById('reset-req-lower');
const resetReqNumber = document.getElementById('reset-req-number');
const resetReqSpecial = document.getElementById('reset-req-special');
const resetMatchHint = document.getElementById('resetPasswordMatchHint');

function setReqReset(el, ok) {
    if (!el) return;
    el.classList.toggle('text-green-600', ok);
    el.classList.toggle('text-gray-500', !ok);
}

function updateResetStrength() {
    const val = resetPwd.value;

    setReqReset(resetReqLength, val.length >= 8);
    setReqReset(resetReqUpper, /[A-Z]/.test(val));
    setReqReset(resetReqLower, /[a-z]/.test(val));
    setReqReset(resetReqNumber, /[0-9]/.test(val));
    setReqReset(resetReqSpecial, /[^A-Za-z0-9]/.test(val));
}

function updateResetMatch() {
    const v1 = resetPwd.value;
    const v2 = resetConfirm.value;

    if (!v1 && !v2) {
        resetMatchHint.textContent = "";
        resetMatchHint.className = "text-xs text-gray-500";
        return;
    }

    if (v1 === v2) {
        resetMatchHint.textContent = "Passwords match.";
        resetMatchHint.className = "text-xs text-green-600";
    } else {
        resetMatchHint.textContent = "Passwords do not match.";
        resetMatchHint.className = "text-xs text-red-500";
    }
}

if (resetPwd) resetPwd.addEventListener("input", () => { updateResetStrength(); updateResetMatch(); });
if (resetConfirm) resetConfirm.addEventListener("input", updateResetMatch);