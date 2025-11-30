// ------------------------------
// GLOBAL PASSWORD VISIBILITY LOGIC
// ------------------------------

const eyeOpen = `
<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" 
     viewBox="0 0 24 24">
  <path stroke-linecap="round" stroke-linejoin="round"
        d="M2.458 12C3.732 7.943 7.523 5 12 5
           c4.477 0 8.268 2.943 9.542 7
           -1.274 4.057-5.065 7-9.542 7
           -4.477 0-8.268-2.943-9.542-7z" />
  <circle cx="12" cy="12" r="3"
        stroke-linecap="round" stroke-linejoin="round" />
</svg>
`;

const eyeClosed = `
<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
     viewBox="0 0 24 24">
  <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18"/>
  <path stroke-linecap="round" stroke-linejoin="round"
        d="M10.584 10.587A3 3 0 0113.412 13.41
           M6.883 6.883C4.943 8.173 3.5 10.063 2.458 12
           c1.274 4.057 5.065 7 9.542 7
           1.466 0 2.853-.294 4.104-.828" />
  <path stroke-linecap="round" stroke-linejoin="round"
        d="M17.117 17.117C19.057 15.827 20.5 13.937 21.542 12
           c-1.274-4.057-5.065-7-9.542-7
           -1.466 0-2.853.294-4.104.828" />
</svg>
`;

// -------------
// Initialize all password-toggle buttons
// -------------
document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll(".password-toggle").forEach(btn => {
        btn.innerHTML = eyeOpen; // default icon
    });
});

// -------------
// Toggle visibility on click
// -------------
document.addEventListener("click", (e) => {
    const btn = e.target.closest(".password-toggle");
    if (!btn) return;

    const targetId = btn.getAttribute("data-target");
    const input = document.getElementById(targetId);
    if (!input) return;

    const isHidden = input.type === "password";
    input.type = isHidden ? "text" : "password";

    btn.innerHTML = isHidden ? eyeClosed : eyeOpen;
});